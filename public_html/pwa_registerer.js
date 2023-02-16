
document.addEventListener("DOMContentLoaded", () => {
    if (NOTIFICATIONS_ENABLED) {
        pnSubscribe();
    } else {
        registerServiceWorker();
    }
});

async function registerServiceWorker() {
    if ('serviceWorker' in navigator) {
        try {
            await navigator.serviceWorker.register(root + "/pwa_service_worker.js");
        } catch (e) {
            console.log("Service Worker registration failed");
        }
    }
}

function pushNotificationAvailable() {
    var bAvailable = false;
    if (window.isSecureContext) {
        // running in secure context - check for available Push-API
        bAvailable = (('serviceWorker' in navigator) &&
            ('PushManager' in window) &&
            ('Notification' in window));
    } else {
        console.log('Site have to run in secure context!');
    }
    return bAvailable;
}

async function pnSubscribe() {
    if (pushNotificationAvailable()) {
        navigator.permissions.query({ name: 'notifications' }).then(function (notificationPerm) {
            notificationPerm.onchange = function () {
                navigator.serviceWorker.getRegistration()
                    .then(function (registration) {
                        if(notificationPerm.state == "granted"){
                            registration.unregister().then(() => {
                                location.reload();
                            });
                        }
                    });
            };
        });
        // if not granted or denied so far...
        if (window.Notification.permission === 'default') {
            await window.Notification.requestPermission();
        }
        registerServiceWorker();
    }
}
