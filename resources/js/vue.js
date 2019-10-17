require('./bootstrap');

window.Vue = require('vue');

import VueRouter from 'vue-router';
import Toasted from 'vue-toasted';
import store from './stores/global-store';
import BootstrapVue from 'bootstrap-vue';
import VueSocketio from 'vue-socket.io';
import Notify from 'bootstrap-notify';

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

Vue.use(VueRouter);
Vue.use(Toasted);
Vue.use(BootstrapVue);
Vue.use(Notify);
Vue.use(new VueSocketio({
	debug: 'true',
	connection: 'http://192.168.10.10:8080',
}));

const menu = Vue.component('item-list', require('./components/ItemList.vue'));
const users = Vue.component('user-list', require('./components/manager/UserList.vue'));
const tables = Vue.component('table-list', require('./components/manager/TableList.vue'));
const dashboard = Vue.component('dashboard', require('./components/manager/Dashboard.vue'));
const meals = Vue.component('meal-list', require('./components/waiter/MealList.vue'));
const waiterOrders = Vue.component('waiter-orders', require('./components/waiter/Orders.vue'));
const profile = Vue.component('user-profile', require('./components/worker/profile.vue'));
const confirm = Vue.component('user-confirm', require('./components/worker/confirm.vue'));
const notifications = Vue.component('user-notifications', require('./components/worker/notifications.vue'));
const pendingInvoices = Vue.component('invoices-pending', require('./components/cashier/PendingInvoices.vue'));
const allInvoices = Vue.component('invoices-all', require('./components/cashier/AllInvoices.vue'));
const cookOrders = Vue.component('cook-orders', require('./components/cook/orders.vue'));
const managerMeals = Vue.component('manager-meals-list', require('./components/manager/MealList.vue'));

const routes = [
{ path: '/', component: menu, name: 'menu'},
{ path: '/manager/users', component: users, name: 'users'},
{ path: '/manager/tables', component: tables, name: 'tables'},
{ path: '/manager/dashboard', component: dashboard, name: 'dashboard'},
{ path: '/waiter/mymeals', component: meals, name: 'meals'},
{ path: '/waiter/orders', component: waiterOrders, name: 'waiterOrders'},
{ path: '/profile', component: profile, name: 'profile'},
{ path: '/confirm/:activationToken', component: confirm, name: 'confirm'},
{ path: '/notifications', component: notifications, name: 'notifications'},
{ path: '/invoices/pending', component: pendingInvoices, name: 'pendingInvoices'},
{ path: '/invoices/all', component: allInvoices, name: 'allInvoices'},
{ path: '/cook/orders', component: cookOrders, name: 'cookOrders'},
{ path: '/manager/meals', component: managerMeals, name: 'managerMeals'},
];

const router = new VueRouter({
    routes:routes
});

router.beforeEach((to, from, next) =>{
	if(to.name == 'users' || to.name == 'tables'  || to.name == 'dashboard' || to.name == 'managerMeals'){
		let user = store.state.user;
		if(user == null || user.type != "manager"){
			next("/");
			app.toastPopUp("error", "Not a manager");
			return;
		}
	}
  if(to.name == 'meals' || to.name == 'waiterOrders' || to.name == 'waiterPreparedOrders'){
      let user = store.state.user;
      if(user == null || user.type != "waiter"){
          next("/");
          app.toastPopUp("error", "Not a waiter");
          return;
      }
  }
	if(to.name == 'profile' || to.name == 'notifications'){
		let user = store.state.user;
		if(!user){
			next("/");
			app.toastPopUp("error", "Not logged in");
			return;
		}
	}
	if(to.name == 'pendingInvoices' || to.name == 'allInvoices'){
		let user = store.state.user;
		if(!user || user.type != "cashier"){
			next("/");
			app.toastPopUp("error", "Not a cashier");
			return;
		}
	}
  if(to.name == "cookOrders"){
    let user = store.state.user;
    if(!user || user.type != "cook"){
      next("/");
      app.toastPopUp("error", "Not a cook");
      return;
    }
  }
	next();
	return;
});

var common = {
    methods: {
    	toastPopUp(type, msg){
	        Vue.toasted.clear();
	        if(type == "show"){
	          	Vue.toasted.show(msg, {
	            	position: "bottom-center",
	              	duration: 3000,
	          });
	          return;
	        }
	        if(type == "error"){
	          	Vue.toasted.error(msg, {
	            	position: "bottom-center",
	              	duration: 3000,
	          	});
	          	return;
	        }
	        if(type == "success"){
	          	Vue.toasted.success(msg, {
	            	position: "bottom-center",
	              	duration: 3000,
	          	});
	          	return;
	        }
	    },
	    getUserPhoto(url){
	    	return "storage/profiles/" + url;
	   	},
	},
    computed:{
        getAuthUser: function(){
            console.log(this.$store.state.user);
            return this.$store.state.user;
        },
    },
}

Vue.mixin(common);

const app = new Vue({
    router,
    store,
    sockets: {
    	receive_notifications(notifications){

    	},
    	new_order_cooks(notification){
        $.notify(
          {message: notification.message,url: notification.url,},
          {type: 'info',newest_on_top: true,placement: {from: "top",align: "center"},timer: 1000,animate: {enter: 'animated fadeInDown',exit: 'animated fadeOutUp'},url_target: '_self',}
        );
    	},
    	new_order_sent(message){
    		app.toastPopUp("success", message);
    	},
    	prepared_order_waiter(notification){
        $.notify(
          {message: notification.message,url: notification.url,},
          {type: 'info',newest_on_top: true,placement: {from: "top",align: "center"},timer: 1000,animate: {enter: 'animated fadeInDown',exit: 'animated fadeOutUp'},url_target: '_self',}
        );
    	},
    	prepared_order_unavailable(message){
    		app.toastPopUp("error", message);
    	},
    	prepared_order_sent(message){
    		app.toastPopUp("success", message);
    	},
    	new_invoice_cashiers(notification){
        $.notify(
          {message: notification.message,url: notification.url,},
          {type: 'info',newest_on_top: true,placement: {from: "top",align: "center"},timer: 1000,animate: {enter: 'animated fadeInDown',exit: 'animated fadeOutUp'},url_target: '_self',}
        );
    	},
    	new_invoice_sent(message){
    		app.toastPopUp("success", message);
    	},
    	meal_paid_managers(notification){
        $.notify(
          {message: notification.message,url: notification.url,},
          {type: 'info',newest_on_top: true,placement: {from: "top",align: "center"},timer: 1000,animate: {enter: 'animated fadeInDown',exit: 'animated fadeOutUp'},url_target: '_self',}
        );
    	},
    	meal_paid_sent(message){
    		app.toastPopUp("success", message);
    	},
    	manager_situation(notification){
        $.notify(
          {message: notification.message,url: notification.url,},
          {type: 'info',newest_on_top: true,placement: {from: "top",align: "center"},timer: 1000,animate: {enter: 'animated fadeInDown',exit: 'animated fadeOutUp'},url_target: '_self',}
        );
    	},
    	manager_situation_sent(message){
    		app.toastPopUp("success", message);
    	},
    },
}).$mount('#app');
