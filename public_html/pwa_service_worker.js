const cacheName = 'cdb-app';
const staticAssets = [];

self.addEventListener('install', async e => {
  const cache = await caches.open(cacheName);
  await cache.addAll(staticAssets);
  return self.skipWaiting();
});

self.addEventListener('activate', e => {
  self.clients.claim();
  pnSubscribe();
});

self.addEventListener('fetch', async e => {
  const req = e.request;
  const url = new URL(req.url);

  if (url.origin === location.origin) {
    e.respondWith(cacheFirst(req));
  } else {
    e.respondWith(networkAndCache(req));
  }
});

async function cacheFirst(req) {
  const cache = await caches.open(cacheName);
  const cached = await cache.match(req);
  return cached || fetch(req);
}

async function networkAndCache(req) {
  const cache = await caches.open(cacheName);
  try {
    const fresh = await fetch(req);
    await cache.put(req, fresh.clone());
    return fresh;
  } catch (e) {
    const cached = await cache.match(req);
    return cached;
  }
}

/**
  * encode the public key to Array buffer
  * @param {string} strBase64  -   key to encode
  * @return {Array} - UInt8Array
  */
function encodeToUint8Array(strBase64) {
  var strPadding = '='.repeat((4 - (strBase64.length % 4)) % 4);
  strBase64 = (strBase64 + strPadding).replace(/\-/g, '+').replace(/_/g, '/');
  var rawData = atob(strBase64);
  var aOutput = new Uint8Array(rawData.length);
  for (i = 0; i < rawData.length; ++i) {
    aOutput[i] = rawData.charCodeAt(i);
  }
  return aOutput;
}

/**
* event listener to subscribe notifications and save subscription at server
* @param {ExtendableEvent} event 
*/
async function pnSubscribe(event) {
  try {
    let response = (await fetch(self.registration.scope + "notifications/vapidKey"));
    let publicVapidKey = (await response.json()).data;
    var appPublicKey = encodeToUint8Array(publicVapidKey);
    var opt = {
      applicationServerKey: appPublicKey,
      userVisibleOnly: true
    };
    self.registration.pushManager.subscribe(opt)
      .then((sub) => {
        // subscription succeeded - send to server
        pnSaveSubscription(sub)
          .then((response) => {
            console.log(response);
          });
      }).catch((e) => {
        
      });

  } catch (e) {

  }
}

/**
* event listener handling when subscription change
* just re-subscribe 
* @param {PushSubscriptionChangeEvent} event 
*/
async function pnSubscriptionChange(event) {
  try {
    // re-subscribe with old options
    self.registration.pushManager.subscribe(event.oldSubscription.options)
      .then((sub) => {
        // subscription succeeded - send to server
        pnSaveSubscription(sub)
          .then((response) => {
            console.log(response);
          });
      });

  } catch (e) {
  }
}

/**
* save subscription on server
* using Fetch API to send subscription infos to the server
* subscription is encance with the userAgent for internal use on the server
* @param {object} sub - PushSubscription
* @return {string} - response of the request
*/
async function pnSaveSubscription(subscription) {
  // stringify object to post as body with HTTP-request
  var fetchData = {
    method: 'post',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(subscription),
  };
  // we're using fetch() to post the data to the server
  var response = await fetch(self.registration.scope + "notifications/saveSubscription", fetchData);
  return await response.json();
}
/**
* event listener to show notification
* @param {PushEvent} event
*/
function pnPushNotification(event) {
  console.log('push event: ' + event);
  var strTitle = "";
  var oPayload = null;
  var opt = { icon: self.registration.scope + "assets/logo.png" };
  if (event.data) {
    // PushMessageData Object containing the pushed payload
    try {
      // try to parse payload JSON-string
      oPayload = JSON.parse(event.data.text());
    } catch (e) {
      // if no valid JSON Data take text as it is...
      // ... comes maybe while testing directly from DevTools
      opt = {
        icon: self.registration.scope + "assets/logo.png",
        body: event.data.text(),
      };
    }
    if (oPayload) {
      if (oPayload.title !== undefined && oPayload.title !== '') {
        strTitle = oPayload.title;
      }
      opt = oPayload.opt;
      if (oPayload.opt.icon === undefined ||
        oPayload.opt.icon === null ||
        oPayload.icon === '') {
        // if no icon defined, use default
        opt.icon = self.registration.scope + "assets/logo.png";
      }
    }
  }
  var promise = self.registration.showNotification(strTitle, opt);
  event.waitUntil(promise);
}

/**
* event listener to notification click
* if URL passed, just open the window...
* @param {NotificationClick} event
*/
function pnNotificationClick(event) {
  if (event.notification.data && event.notification.data.url) {
    const promise = clients.openWindow(event.notification.data.url);
    event.waitUntil(promise);
    notification.close();
  }
}

/**
* event listener to notification close
* ... if you want to do something for e.g. analytics
* @param {NotificationClose} event
*/
function pnNotificationClose(event) {
}

/**=========================================================
* add all needed event-listeners
* - activate:  subscribe notifications and send to server
* - push:      show push notification
* - click:     handle click an notification and/or action 
*              button
* - change:    subscription has changed
* - close:     notification was closed by the user
*=========================================================*/
// and listen to incomming push notifications
self.addEventListener('push', pnPushNotification);
// ... and listen to the click
self.addEventListener('notificationclick', pnNotificationClick);
// subscription has changed
self.addEventListener('pushsubscriptionchange', pnSubscriptionChange);
// notification was closed without further action
self.addEventListener('notificationclose', pnNotificationClose);