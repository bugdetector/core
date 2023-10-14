
document.addEventListener("DOMContentLoaded", () => {
    if (NOTIFICATIONS_ENABLED) {
        showPnSubscribeMessage();
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

function showPnSubscribeMessage(){
    // if not granted or denied so far...
    if(window.Notification.permission === 'default' && !PN_DENIED){
        let [modal, modalContent] = openModal(
            _t("subscribe_notifications"),
            _t("subscribe_notifications_message"),
            `<div class="d-flex w-100">
                <div class="w-50 px-2">
                    <button type="button" class="btn btn-light-danger w-100 deny-pn" data-bs-dismiss="modal">${_t("thanks")}</button>
                </div>
                <div class="w-50 px-2">
                    <button type="button" class="btn btn-primary w-100 subscribe-pn">${_t("allow_notifications")}</button>
                </div>
            </div>`,
            "modal-md",
            false
        );
        modalContent.find(".subscribe-pn").on("click", function(e){
            pnSubscribe();
            modal.hide();
            modalContent.remove();
        })
        modalContent.find(".deny-pn").on("click", function(){
            fetch(root + "/notifications/denySubscription");
            modal.hide();
            modalContent.remove();
        })
    } else if(window.Notification.permission === 'granted'){
        registerServiceWorker();
    }
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
        await window.Notification.requestPermission();
        registerServiceWorker();
    }
}
