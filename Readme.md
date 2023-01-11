# CONTENTS OF THIS FILE
 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Override Settings

# INTRODUCTION
Purpose of this module is to integrate sprinklr chatbot on the site.

# REQUIREMENTS
It requires only Drupal CORE.

# INSTALLATION
Install as any other contrib module, no specific configuration required for
installation.

# CONFIGURATION
* Visit /admin/config/system/sprinklr to configure.
* Uncheck the "Enable sprinklr chatbot" checkbox to disable the feature.
* Enter "App Id" to connect with sprinklr chatbot.
  For Multilingual Site - Add translations for "App Id" /admin/config/system/sprinklr/translate 
  if different App Ids are required.
* Configure URLs list to enable sprinklr chatbot on specific pages or to disable it on
  some pages.
* Choose content types to enable it for all the nodes of chosen content types.
* Click "Save configuration" to apply your changes.

# OVERRIDE SETTINGS
It is possible to override chatbot feature by implementing an event listener like the
example below:

```
document.addEventListener('sprChatSettingsAlter', (e) => {
  const sprChatSettings = e.detail;
  // Set userName value in sprChatSettings variable.
  sprChatSettings.userName = 'John';
});
```
