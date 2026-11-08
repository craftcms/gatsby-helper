import {actionClient, cpConfig, onPreviewEvent} from '@craftcms/cp';

/**
 * Tells Gatsby about Live Preview changes, as Gatsby Helper 2.x did: each time the
 * preview frame reloads, the Preview Webhook URL gets the element and the preview
 * token, and when the preview closes it gets the element again, without a token,
 * so Gatsby goes back to the published content.
 */
let current = null;

async function notify(url, payload) {
  try {
    await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'x-preview-update-source': 'Craft CMS',
      },
      body: JSON.stringify(payload),
    });
  } catch (e) {
    console.warn('Gatsby Helper could not reach the Preview Webhook URL.', e);
  }
}

onPreviewEvent('after-update-iframe', async ({iframe}) => {
  const token = new URL(iframe.src, window.location.href).searchParams.get(
    cpConfig().get('tokenParam', 'token')
  );

  if (!token) {
    return;
  }

  const {status, data} = await actionClient.get(
    'gatsby-helper/preview-payload',
    {
      params: {previewToken: token},
    }
  );

  if (status === 204 || !data?.url) {
    return;
  }

  current = data;
  await notify(data.url, data.payload);
});

function restore() {
  if (!current) {
    return;
  }

  const {url, payload} = current;
  current = null;
  const {token: _, ...published} = payload;
  void notify(url, published);
}

onPreviewEvent('before-close', restore);
window.addEventListener('beforeunload', restore);
