<script setup>
    import { ref, onMounted } from 'vue';
    import { router } from "@inertiajs/vue3";
    
    const props = defineProps({
        appId: {
            type: String
        },
        configId: {
            type: String
        }
    })

    onMounted(() => {
        window.fbAsyncInit = function () {
            // JavaScript SDK configuration and setup
            FB.init({
                appId: props.appId, // Facebook App ID
                cookie: true, // enable cookies
                xfbml: true, // parse social plugins on this page
                version: 'v20.0' // Graph API version
            });
        };

        // Load the JavaScript SDK asynchronously
        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    });

    const sessionInfoListener = (event) => {
        if (event.origin !== "https://www.facebook.com" && event.origin !== "https://web.facebook.com") {
            return;
        }
        
        try {
            const data = JSON.parse(event.data);
            if (data.type === 'WA_EMBEDDED_SIGNUP') {
                // if user finishes the Embedded Signup flow
                if (data.event === 'FINISH') {
                    const {phone_number_id, waba_id} = data.data;
                }
                // if user cancels the Embedded Signup flow
                else {
                    const{current_step} = data.data;
                }
            }
        } catch {
            // Don’t parse info that’s not a JSON
            console.log('Non JSON Response', event.data);
        }
    };

    function launchWhatsAppSignup() {
        window.addEventListener("message", sessionInfoListener);

        // Conversion tracking code
        if (typeof fbq !== 'undefined') {
            fbq('trackCustom', 'WhatsAppOnboardingStart', {
                appId: props.appId,
                feature: 'whatsapp_embedded_signup'
            });
        }

        // Launch Facebook login
        FB.login(function (response) {
            if (response.authResponse) {
                console.log(response.authResponse);
                router.post('/whatsapp/exchange-code', {
                    token: response.authResponse.code,
                }, {
                    preserveState: false,
                })
            } else {
                //console.log('User cancelled login or did not fully authorize.');
            }
        }, {
            config_id: props.configId, // configuration ID goes here
            response_type: 'code', // must be set to 'code' for System User access token
            override_default_response_type: true, // when true, any response types passed in the "response_type" will take precedence over the default types
            extras: {
                sessionInfoVersion: 2,
                setup: {
                    // Prefilled data can go here
                }
            }
        });
    }
</script>
<template>
    <button @click="launchWhatsAppSignup" class="bg-primary text-white p-2 rounded-lg text-sm mt-5 flex px-3 w-fit">
        {{ $t('Setup whatsapp') }}
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><g fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"><g opacity=".2"><path d="M12.206 5.848a1.5 1.5 0 0 1 2.113.192l3.333 4a1.5 1.5 0 1 1-2.304 1.92l-3.334-4a1.5 1.5 0 0 1 .192-2.112Z"/><path d="M12.206 16.152a1.5 1.5 0 0 1-.192-2.112l3.334-4a1.5 1.5 0 0 1 2.304 1.92l-3.333 4a1.5 1.5 0 0 1-2.113.192Z"/><path d="M16 11a1.5 1.5 0 0 1-1.5 1.5h-8a1.5 1.5 0 0 1 0-3h8A1.5 1.5 0 0 1 16 11Z"/></g><path d="M11.347 5.616a.5.5 0 0 1 .704.064l3.333 4a.5.5 0 0 1-.768.64l-3.333-4a.5.5 0 0 1 .064-.704Z"/><path d="M11.347 14.384a.5.5 0 0 1-.064-.704l3.333-4a.5.5 0 0 1 .768.64l-3.333 4a.5.5 0 0 1-.704.064Z"/><path d="M15.5 10a.5.5 0 0 1-.5.5H5a.5.5 0 0 1 0-1h20a.5.5 0 0 1 .5.5Z"/></g></svg>
    </button>
</template>