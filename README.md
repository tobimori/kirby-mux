# Kirby Mux

Experimental plugin for adding Mux video support to Kirby.

## Setup

### 1. Install the Plugin

Add the plugin to your Kirby project (installation method will be added when the plugin is published).

### 2. Configure API Keys

Add your Mux API credentials to your Kirby config file:

```php
<?php
// site/config/config.php

return [
  'tobimori.mux' => [
    'tokenId' => 'XXX',        // Your Mux API Access Token ID
    'tokenSecret' => 'XXX',    // Your Mux API Secret Key  
    'signingSecret' => 'XXX'   // Your webhook signing secret
  ]
];
```

### 3. Set Up Webhooks

In your Mux dashboard, create a webhook with the following URL:
```
https://yourdomain.com/mux-endpoint
```

### 4. Create a Page Model

Create a page model that uses the `HasMuxFiles` trait:

```php
<?php
// site/models/videos.php

use tobimori\Mux\HasMuxFiles;

class VideosPage extends \Kirby\Cms\Page
{
    use HasMuxFiles;
}
```

### 5. Create a Blueprint

Create a blueprint for managing video files:

```yaml
# site/blueprints/pages/videos.yml
title: Videos
image:
  icon: file-video

sections:
  files:
    label: Videos
    type: files
    layout: cards
    template: mux-video
    uploads:
      template: mux-video
    image:
      ratio: 16/9
    search: true
    sortBy: title desc
    text: "{{ file.title.or(file.filename) }}"
    info: "{{ file.niceDuration }}"
```

## Usage

### Displaying Videos

Use the Mux video player in your templates:

```php
<mux-video playback-id="<?= $page->video()->toFile()->playbackId()->getId() ?>"></mux-video>
```

Make sure to include the Mux player library in your frontend.

## Limitations

- Uploading from localhost doesn't work; please use a tunnel (e.g. ngrok/expose)
- Works with public (unsigned) playback ids only
