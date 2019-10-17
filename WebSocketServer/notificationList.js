class NotificationList {
	constructor(){
		this.notifications = [];
	}
	
	addUserNotification(notification, user){
		if(this.notifications[user.id]){
			this.notifications[user.id].push(notification);
		}else{
			this.notifications[user.id] = [notification];
		}
	}

	addWorkerTypeNotification(notification, type){
		if(this.notifications[type]){
			this.notifications[type].push(notification);
		}else{
			this.notifications[type] = [notification];
		}
	}

	getNotifications(user){
		var userNotifications = {};
		if(this.notifications[user.id]){
			userNotifications[user.id] = this.notifications[user.id];
		}
		if(this.notifications[user.type]){
			userNotifications[user.type] = this.notifications[user.type];
		}
		return userNotifications;
	}
}

module.exports = NotificationList;