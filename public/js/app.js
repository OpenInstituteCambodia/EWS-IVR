/**
 * Created by keodina on 8/11/16.
 */
var vm = new Vue({
    el: '#home',
    data: {
        name: 'Vue.js',
        phone: '',
        sound: '',
    },
    methods: {
        greeting: function () {
            this.$http.get('api/task', function (response) {
                console.log(response);
            });
        },
        getTask: function () {
            this.$http.get('api/task', function (response) {
                console.log(response);
            });
        },
        makeCall: function () {
            this.$http.post('api/makeCall', {phone: this.phone}).success(function (response) {
                console.log(response);
            }).error(function (response) {
                console.log(response);
            });
        },
        processData: function (event) {
            event.preventDefault();
            var form = new FormData(document.querySelector('form'));
            form.append('api_token', 'C5hMvKeegj3l4vDhdLpgLChTucL9Xgl8tvtpKEjSdgfP433aNft0kbYlt77h');
            form.append('contacts', '[{"phone":"086234665"}]');
            form.append('sound_url', 'https://s3-ap-southeast-1.amazonaws.com/twilio-ews-resources/sounds/2016-11-10:15:43:45_Pursat_02.mp3');
            form.append('activity_id',3);
            form.append('no_of_retry',3);
            form.append('retry_time', 10);
                this.$http.post('http://a64a5e0a.ngrok.io/api/v1/processDataUpload', form, {emulateHTTP: true}).success(function (response) {
                console.log(JSON.stringify(response));
            }).error(function (response) {
                console.log(JSON.stringify(response));
            });
        }
    },
});
Vue.config.devtools = true;