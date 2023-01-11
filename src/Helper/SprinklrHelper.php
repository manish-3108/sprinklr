<?php

namespace Drupal\sprinklr\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * General Helper service for the sprinklr chatbot feature.
 */
class SprinklrHelper {

  /**
   * Config factory service.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Current path service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructor for the SprinklrHelper service.
   *
   * @param Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path service.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    CurrentPathStack $current_path,
    AliasManagerInterface $alias_manager,
    PathMatcherInterface $path_matcher,
    RouteMatchInterface $route_match
  ) {
    $this->configFactory = $config_factory;
    $this->currentPath = $current_path;
    $this->aliasManager = $alias_manager;
    $this->pathMatcher = $path_matcher;
    $this->routeMatch = $route_match;
  }

  /**
   * Detects if the Sprinklr feature is enabled or not.
   *
   * @return bool
   *   Boolean TRUE if sprinklr feature is enabled and FALSE if not.
   */
  public function isSprinklrFeatureEnabled() {
    return $this->configFactory->get('sprinklr.settings')->get('sprinklr_enabled');
  }

  /**
   * Checks if sprinklr feature is enabled for current path or not.
   *
   * @return bool
   *   TRUE if sprinklr is enabled for the current path and
   *   FALSE if not.
   */
  public function isSprinklrEnabledOnCurrentPath() {
    $config = $this->configFactory->get('sprinklr.settings');

    $current_path = $this->currentPath->getPath();
    $path_alias = $this->aliasManager->getAliasByPath($current_path);

    $allowed_urls = trim($config->get('allowed_urls'));
    // If allowed URLs are not set, skip all pages.
    if (!$allowed_urls) {
      return FALSE;
    }
    // If allowed URLs list does not contain current path and skip
    // option is not slected.
    if (!$this->pathMatcher->matchPath($path_alias, $allowed_urls)
      && $config->get('urls_negate')) {
      return $this->isSprinklrEnabledForCurrentNode();
    }
    // If allowed URLs list contains current path and skip option
    // is not selected.
    if ($this->pathMatcher->matchPath($path_alias, $allowed_urls)
      && !$config->get('urls_negate')) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Checks if sprinklr feature is enabled for current node or not.
   *
   * @return bool
   *   TRUE if sprinklr is enabled for the current node page and
   *   FALSE if not.
   */
  public function isSprinklrEnabledForCurrentNode() {
    // Check if current page is a node page.
    if ($this->routeMatch->getRouteName() == 'entity.node.canonical') {
      $node = $this->routeMatch->getParameter('node');
      if ($node instanceof NodeInterface) {
        return in_array(
          $node->bundle(),
          (array) $this->configFactory->get('sprinklr.settings')->get('allowed_content_types')
        );
      }
    }
    return FALSE;
  }

}
