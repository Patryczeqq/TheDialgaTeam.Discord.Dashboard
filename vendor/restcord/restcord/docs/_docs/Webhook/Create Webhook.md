---
title: Create Webhook
category: Webhook
order: 1
---

# `createWebhook`

```php
$client->webhook->createWebhook($parameters);
```

## Description

Create a new webhook. Requires the &#039;MANAGE_WEBHOOKS&#039; permission.

## Parameters


Name | Type | Required | Default
--- | --- | --- | ---
channel.id | snowflake | true | *null*
name | string | false | *null*
avatar | string | false | *null*

## Response

Returns a webhook object on success.

Can Return:

* webhook
