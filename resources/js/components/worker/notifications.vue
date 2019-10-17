<template>
	<div>
		<navigation></navigation>
		<button class="btn btn-primary" name="btnNotifManag" href="#notifyManagers" data-toggle="modal" data-target="#notifyManagers">Notify Managers</button>
		<ul class="notifications">
		  <li class="notification" v-for="notification in orderedNotif">
		      <div class="media">
		      	<router-link v-if="notification.url" v-bind:to="notification.url.slice(1)"><img src="https://image.flaticon.com/icons/png/128/181/181539.png" height="50" width="50" class="mr-2 img-circle" alt="Name"></router-link>
		        <div class="media-body">
		          <strong class="notification-title"><b>{{notification.from}}</b> notified <a>{{notification.to}}</a></strong>
		          <p class="notification-desc">{{notification.message}}</p>

		          <div class="notification-meta">
		            <small class="timestamp">{{notification.date}}</small>
		          </div>
		        </div>
		      </div>
		  </li>
		  <notify-managers @notif-sent="getNotifications"></notify-managers>
		</ul>
	</div>
</template>

<script type="text/javascript">
	import navigation from '../navigation.vue';
	import notifyManagers from './notifyManagers.vue';

	export default {
		components: {
			'navigation': navigation,
			'notify-managers': notifyManagers,
		},
		data: function(){
			return{
				notifications: [],
			}
		},
		methods: {
			getNotifications(){
				this.$socket.emit('receive_notifications', this.$store.state.user);
			}
		},
		sockets: {
			get_notifications(notifications){
				if(notifications != null){
					this.notifications = [];
					for(var index in notifications){
						if(Array.isArray(notifications[index])){
							for(var inner_array in notifications[index]){
								this.notifications.push(notifications[index][inner_array]);
							}
						}
					}
				}
			}
		},
		mounted(){
			this.getNotifications();
		},
		computed: {
			orderedNotif: function(){
				return _.orderBy(this.notifications, 'date', 'desc');
			}
		},
	}
</script>