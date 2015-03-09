.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _configuration:

Configuration Reference
=======================


.. _configuration-typoscript:

TypoScript Reference
--------------------

Use the following TypoScript constants and setup to configure the extension. All properties are to be prefixed with :typoscript:`plugin.tx_streamovationsvp.`

Constants
^^^^^^^^^

These constants are all editable in the Template module's Constant Editor.

.. warning::

   Some of the constants are used on multiple occassions throughout the Static TypoScript setup. It is therefore recommended to set values always via these constants, unless you're overruling **all** of their references in the Static TypoScript.

.. container:: ts-properties

	================================= ========= =============================================================================
	Property                          Data type Default value
	================================= ========= =============================================================================
	`view.templateRootPath`_          dir       EXT:streamovations_vp/Resources/Private/Templates/
	`view.partialRootPath`_           dir       EXT:streamovations_vp/Resources/Private/Partials/
	`view.layoutRootPath`_            dir       EXT:streamovations_vp/Resources/Private/Layouts/
	`javascript.includeJquery`_       boolean   1
	`javascript.jqueryLib`_           resource  EXT:streamovations_vp/Resources/Public/JavaScript/jQuery/jquery-1.11.1.min.js
	`javascript.jwPlayerLib`_         resource  EXT:streamovations_vp/Resources/Public/JavaScript/jwPlayer/jwplayer.js
	`javascript.smvPlayerLib`_        resource
	`css.frontendFile`_               resource  EXT:streamovations_vp/Resources/Public/Css/frontend.min.css
	`rest.scheme`_                    string    http
	`rest.baseUri`_                   string
	`settings.player`_                integer   1
	`settings.jwPlayer.key`_          string
	`settings.jwPlayer.width`_        string    64%
	`settings.jwPlayer.height`_       string
	`settings.jwPlayer.liveLanguage`_ csv       or,nl,en
	`settings.jwPlayer.smilSupport`_  boolean   1
	`settings.jwPlayer.smilWrap`_     wrap      smil:\|/jwplayer.smil
	`settings.topics.enable`_         boolean   1
	`settings.speakers.enable`_       boolean   1
	`settings.speakers.imgDir`_       dir
	`settings.speakers.imgDefault`_   resource  EXT:streamovations_vp/Resources/Public/Image/speaker-avatar.jpg
	`settings.speakers.imgWidth`_     integer   150
	`settings.speakers.imgHeight`_    integer   150
	`settings.polling.interval`_      integer   5
	================================= ========= =============================================================================

Setup
^^^^^

The following TypoScript setup properties allow for some advanced configuration.

.. container:: ts-properties

	============================================= ========= =============
	Property                                      Data type Default value
	============================================= ========= =============
	`view.templateRootPaths.20`_                  string
	`view.partialRootPaths.20`_                   string
	`view.layoutRootPaths.20`_                    string
	`rest.repository.default.cache.enable`_       boolean   1
	`rest.repository.default.cache.lifetime`_     integer   3600
	`rest.repository.Event.cache.enable`_         boolean
	`rest.repository.Event.cache.lifetime`_       integer
	`rest.repository.Playlist.cache.enable`_      boolean
	`rest.repository.Playlist.cache.lifetime`_    integer
	`rest.repository.Meetingdata.cache.enable`_   boolean
	`rest.repository.Meetingdata.cache.lifetime`_ integer
	============================================= ========= =============

Constants property details
^^^^^^^^^^^^^^^^^^^^^^^^^^

.. only:: html

	.. contents::
		:local:
		:depth: 1


.. _view.templateRootPath:

view.templateRootPath
"""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.view.templateRootPath = path/to/dir/`

Points to the default template directory.

.. tip::

   If you wish to selectively overrule template files, see the setup property `view.templateRootPaths.20`_.

.. _view.partialRootPath:

view.partialRootPath
""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.view.partialRootPath = path/to/dir/`

Points to the default template partial directory.

.. tip::

   If you wish to selectively overrule partial files, see the setup property `view.partialRootPaths.20`_.

.. _view.layoutRootPath:

view.layoutRootPath
"""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.view.layoutRootPath = path/to/dir/`

Points to the default template layout directory.

.. tip::

   If you wish to selectively overrule layout files, see the setup property `view.layoutRootPaths.20`_.

.. _javascript.includeJquery:

javascript.includeJquery
""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.javascript.includeJquery = 1`

Enables the inclusion of the jQuery library on pages containing the plugin. If you already include the jQuery library on those pages, you can disable this inclusion by setting it to 0.

.. _javascript.jqueryLib:

javascript.jqueryLib
""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.javascript.jqueryLib = path/to/file.js`

Location of jQuery library, which is included only if `javascript.includeJquery`_ is enabled.

.. _javascript.jwPlayerLib:

javascript.jwPlayerLib
""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.javascript.jwPlayerLib = path/to/file.js`

Location of JW Player library. You would only change this if you require a different version than the one supplied with this extension.

.. _javascript.smvPlayerLib:

javascript.smvPlayerLib
"""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.javascript.smvPlayerLib = path/to/file.js`

Location of SMV Player library. If you want to use the Streamovations Player instead of the default JW Player, you can set its file location here.

.. note::

   The SMV Player library is not included in this extension. Please inquire at your contact at |company| if you wish to use it.

.. note::

   The SMV Player requires a valid JW Player license key to work. Your contact at |company| can tell you which type of license key is necessary.

.. _css.frontendFile:

css.frontendFile
""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.css.frontendFile = path/to/file.css`

Location of CSS file for frontend styling. If you wish to change the default styling, you could create another file and point to it here.

.. tip::

   If you have custom CSS in an already included CSS file, you can simply enter an empty value here to disable the original styling.

.. _rest.scheme:

rest.scheme
"""""""""""

:typoscript:`plugin.tx_streamovationsvp.rest.scheme = https`

The REST API URI scheme. Suppose your |videocms| REST API is available at the following URL::

  https://stream.mysite.com/api.php

The correct value would then be :code:`https`.

.. _rest.baseUri:

rest.baseUri
""""""""""""

:typoscript:`plugin.tx_streamovationsvp.rest.baseUri = stream.mysite.com`

The REST API base URI. Suppose your |videocms| REST API is available at the following URL::

  https://stream.mysite.com/api.php

The correct value would then be :code:`stream.mysite.com`.

.. _settings.player:

settings.player
"""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.settings.player = 2`

Sets the preferred video player. The following choices are available:

#. JW Player
#. SMV Player

.. important::

   For SMV Player, see `javascript.smvPlayerLib`_ for the requirements.

.. _settings.jwPlayer.key:

settings.jwPlayer.key
"""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.settings.jwPlayer.key = abcdefghijklmnopqrstuvwxyz`

The JW Player license key can be used to unlock additional features in your JW Player instance.

.. _settings.jwPlayer.width:

settings.jwPlayer.width
"""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.settings.jwPlayer.width = 600`

The Player width in pixels or percentage. Only applies if JW Player is the chosen player.

.. note::

   If you wish to change the dimensions of the player when the SMV Player is active, you will need to provide custom CSS setting the width and/or height of the element :code:`.tx-streamovations-vp .video-player`

.. _settings.jwPlayer.height:

settings.jwPlayer.height
""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.settings.jwPlayer.height = 355`

The Player height in pixels or percentage. Only applies if JW Player is the chosen player.

.. note::

   If you wish to change the dimensions of the player when the SMV Player is active, you will need to provide custom CSS setting the width and/or height of the element :code:`.tx-streamovations-vp .video-player`

.. _settings.jwPlayer.liveLanguage:

settings.jwPlayer.liveLanguage
""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.settings.jwPlayer.liveLanguage = en,nl`

The order of preferred languages on livestreams. Only applies if JW Player is the chosen player.

.. _settings.jwPlayer.smilSupport:

settings.jwPlayer.smilSupport
"""""""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.settings.jwPlayer.smilSupport = 1`

If the REST response contains a reference to a .smil file, this setting enables usage. (generally recommended). Only applies if JW Player is the chosen player.

.. _settings.jwPlayer.smilWrap:

settings.jwPlayer.smilWrap
""""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.settings.jwPlayer.smilWrap = smil:|/jwplayer.smil`

The smil file wrap. Only change if you know what you're doing. Only applies if JW Player is the chosen player.

.. note::

   See why this is needed in the `JW Player documentation`_.

.. _settings.topics.enable:

settings.topics.enable
""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.settings.topics.enable = 1`

Enables the use of topics metadata, if available via the REST API of your |videocms|. This requires videos to have correctly assigned topics and associated timestamps in the |videocms|.

.. _settings.speakers.enable:

settings.speakers.enable
""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.settings.speakers.enable = 1`

Enables the use of speakers metadata, if available via the REST API of your |videocms|. This requires videos to have correctly assigned speakers and associated timestamps in the |videocms|.

.. _settings.speakers.imgDir:

settings.speakers.imgDir
""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.settings.speakers.imgDir = path/to/dir/`

If speakers metadata is set with avatar pictures in the |videocms|, then this could be displayed on the website as well. All you need to do, is place the same pictures with the exact same filenames in the directory you configure here.

.. _settings.speakers.imgDefault:

settings.speakers.imgDefault
""""""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.settings.speakers.imgDefault = path/to/file.png`

When a speaker has no picture set, or no matching picture is found in `settings.speakers.imgDir`_, then the default image is shown. You can change the default image with this property.

.. _settings.speakers.imgWidth:

settings.speakers.imgWidth
""""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.settings.speakers.imgWidth = 150`

The avatar width in pixels.

.. _settings.speakers.imgHeight:

settings.speakers.imgHeight
"""""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.settings.speakers.imgHeight = 150`

The avatar height in pixels.

.. _settings.polling.interval:

settings.polling.interval
"""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.settings.polling.interval = 5`

Polling interval in seconds. The interval in which polling is performed to request new topic and/or speaker metadata during livestreams. Polling is only active on livestreams.

.. tip::

   Setting this property to anything below 1, will disable polling on livestreams.


Setup property details
^^^^^^^^^^^^^^^^^^^^^^

.. only:: html

	.. contents::
		:local:
		:depth: 1


.. _view.templateRootPaths.20:

view.templateRootPaths.20
"""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.view.templateRootPaths.20 = path/to/other/dir/`

Points to an overruling template directory. You can selectively overrule one or more template files this way while maintaining a fallback to the original template directory set with the constant property `view.templateRootPath`_.

.. _view.partialRootPaths.20:

view.partialRootPaths.20
""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.view.partialRootPaths.20 = path/to/other/dir/`

Points to an overruling template partial directory. You can selectively overrule one or more partial files this way while maintaining a fallback to the original partial directory set with the constant property `view.partialRootPath`_.

.. _view.layoutRootPaths.20:

view.layoutRootPaths.20
"""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.view.layoutRootPaths.20 = path/to/other/dir/`

Points to an overruling template layout directory. You can selectively overrule one or more layout files this way while maintaining a fallback to the original layout directory set with the constant property `view.layoutRootPath`_.

.. _rest.repository.default.cache.enable:

rest.repository.default.cache.enable
""""""""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.rest.repository.default.cache.enable = 1`

Enables or disables caching for REST-requests as a default for all REST APIs.

.. _rest.repository.default.cache.lifetime:

rest.repository.default.cache.lifetime
""""""""""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.rest.repository.default.cache.lifetime = 3600`

Caching lifetime in seconds. Set this for REST-requests as a default for all REST APIs.

.. tip::

   The ideal setting may differ from the default per use case. Setting a high lifetime prevents redundant bandwith between your website and your |videocms|, but lowers the update frequency of the plugins.

.. _rest.repository.Event.cache.enable:

rest.repository.Event.cache.enable
""""""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.rest.repository.Event.cache.enable = 1`

Enables or disables caching for REST-requests for the Session REST API.

.. _rest.repository.Event.cache.lifetime:

rest.repository.Event.cache.lifetime
""""""""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.rest.repository.Event.cache.lifetime = 3600`

Caching lifetime in seconds. Set this for REST-requests for the Session REST API.

.. _rest.repository.Playlist.cache.enable:

rest.repository.Playlist.cache.enable
"""""""""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.rest.repository.Playlist.cache.enable = 1`

Enables or disables caching for REST-requests for the Playlist REST API.

.. _rest.repository.Playlist.cache.lifetime:

rest.repository.Playlist.cache.lifetime
"""""""""""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.rest.repository.Playlist.cache.lifetime = 3600`

Caching lifetime in seconds. Set this for REST-requests for the Playlist REST API.

.. _rest.repository.Meetingdata.cache.enable:

rest.repository.Meetingdata.cache.enable
""""""""""""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.rest.repository.Meetingdata.cache.enable = 1`

Enables or disables caching for REST-requests for the Meetingdata REST API.

.. note::

   Regardless of this setting, the polling script always executes with this specific cache disabled.

.. _rest.repository.Meetingdata.cache.lifetime:

rest.repository.Meetingdata.cache.lifetime
""""""""""""""""""""""""""""""""""""""""""

:typoscript:`plugin.tx_streamovationsvp.rest.repository.Meetingdata.cache.lifetime = 3600`

Caching lifetime in seconds. Set this for REST-requests for the Meetingdata REST API.


.. _JW Player documentation: http://support.jwplayer.com/customer/portal/articles/1430398-dynamic-rtmp-streaming