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
            form.append('contacts', '[{"phone":"086234665"},{"phone":"017641855"}]');
            form.append('activity_id',3);
            form.append('no_of_retry',3);
            form.append('retry_time', 10);
            this.$http.post('http://ews-twilio.ap-southeast-1.elasticbeanstalk.com/api/v1/processDataUpload', form, {emulateHTTP: true}).success(function (response) {
                console.log(JSON.stringify(response));
            }).error(function (response) {
                console.log(JSON.stringify(response));
            });
        }
    },
});
Vue.config.devtools = true;