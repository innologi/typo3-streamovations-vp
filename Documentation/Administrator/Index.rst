.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _admin-manual:

Administrator Manual
====================


.. _admin-installation:

Installation
------------

The extension can be installed like any other extension from the TER. The easiest method is to go to the Extension Manager and search for the extension '|extkey|'. Once found, import it. The extension has no noteworthy dependencies to other extensions.

Once imported, you need to load the extension's static template. Without it, the plugin cannot function. You can safely set it from a ROOT TS template record. The necessary javascript libraries are loaded exclusively on pages that show content from the plugin.


.. _admin-configuration:

Configuration
-------------

Once the extension is installed, you need to set the TypoScript constants with values relevant to your setup. You are encouraged to use the Template module's Constant Editor for this, as each constant will then be provided with relevant description and/or choices.

.. warning::

   Some of the constants are used on multiple occassions throughout the Static TypoScript setup. It is therefore recommended to set values always via these constants, unless you're overruling **all** of their references in the Static TypoScript.


There are two constants which absolutely are required to be set before the plugin will function:

#. **REST API scheme:** the uri-scheme of the REST API of your |videocms|. (defaults to 'http')
#. **REST API base URI:** the base uri of the REST API of your |videocms|.

If your REST API URL is something like this::

  https://stream.mysite.com/api.php

Then these constants are respectively to be set to the following values:

#. https
#. stream.mysite.com

The end result in the TypoScript template record's *constant* field would then look like this::

  plugin.tx_streamovationsvp.rest.scheme = https
  plugin.tx_streamovationsvp.rest.baseUri = stream.mysite.com

