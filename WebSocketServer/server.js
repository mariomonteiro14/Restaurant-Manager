/*jshint esversion: 6 */

var app = require('http').createServer();


var io = require('socket.io')(app);

var LoggedUsers = require('./loggedusers.js');
var NotificationList = require('./notificationList.js');

app.listen(8080, function(){
    console.log('listening on *:8080');
});

// ------------------------
// Estrutura dados - server
// ------------------------

// loggedUsers = the list (map) of logged users. 
// Each list element has the information about the user and the socket id
// Check loggedusers.js file

let loggedUsers = new LoggedUsers();
let notifications = new NotificationList();

const COOKS_CHANNEL = 'cook_notifications';
const MANAGERS_CHANNEL = 'manager_notifications';
const WAITERS_CHANNEL = 'waiter_notifications';
const CASHIERS_CHANNEL = 'cashier_notifications';

io.on('connection', function (socket) {
    console.log('client has connected (socket ID = '+socket.id+')');

    socket.on('disconnect', function() {
      console.log('client has disconnected (socket ID = '+socket.id+')');
      loggedUsers.removeUserInfoBySocketID(socket.id);
   });

    // On login join Working Users notifications and Worker type notifications
    socket.on('user_login', function(user){
    	if(user !== undefined && user !== null){
    		loggedUsers.addUserInfo(user, socket.id);
    		console.log('User logged in (socket ID = '+socket.id + ')');
    		if(user.shift_active){
    			socket.join('active_notifications');
    			socket.join(user.type + '_notifications');
                console.log("Joined " + user.type + "_notifications channel");
    		}
    	}
    });

    socket.on('receive_notifications', function(user){
        if(user !== undefined && user !== null && user.shift_active == true){
            console.log('Sending notifications to user...');
            socket.emit('get_notifications', notifications.getNotifications(user));
        }
    });

    //On logout leave active workers channels and get remove from loggedUsers
    socket.on('user_logout', function(user){
    	if(user !== undefined && user !== null){
    		if(user.shift_active){
    			socket.leave('active_notifications');
    			socket.leave(user.type + '_notifications');
    		}
    		loggedUsers.removeUserInfoBySocketID(socket.id);
    		console.log('User logged out (socket ID = '+socket.id + ')');
    	}	
    });

    //On start of shift join notifications channels
    socket.on('shift_started', function(user){
        if(user !== undefined && user !== null){
            if(user.shift_active){
                socket.join('active_notifications');
                socket.join(user.type + '_notifications');
                console.log("Joined " + user.type + "_notifications channel");
            }
        }
    });

    //On end of shift leave notifications channels
    socket.on('shift_ended', function(user){
        if(user !== undefined && user !== null){
            if(!user.shift_active){
                socket.leave('active_notifications');
                socket.leave(user.type + '_notifications');
                console.log("Left " + user.type + "_notifications channel");
            }
        }
    });

    //Notification from Waiter to all Cooks with an order
    socket.on('new_order', function(notification){
        if(notification != null){
            io.sockets.to(COOKS_CHANNEL).emit('new_order_cooks', notification);
            notifications.addWorkerTypeNotification(notification, 'cook');
            console.log('New order arrived, notifying cooks...');
            socket.emit('new_order_sent', 'New order notification sent');
        }
    });

    //Update waiter list
    socket.on('update', function(waiter_id){
        let waiter = loggedUsers.userInfoByID(waiter_id);
        let waiter_socketId = waiter !== undefined ? waiter.socketID : null
        if(waiter_socketId != null){
            console.log(waiter_socketId);
            io.to(waiter_socketId).emit('update_list');
        }
    });

    //Update cashiers list when meal is marked as not paid
    socket.on('not_paid', function(){
        io.sockets.to(CASHIERS_CHANNEL).emit('update_invoices');
    })

    //Cook that selected order notifies responsible waiter that order is prepared
    socket.on('prepared_order', function(notification, toUser){
        if(notification != null){
            console.log('Order prepared, notifying responsible waiter...');
            let waiter = loggedUsers.userInfoByID(toUser);
            let waiter_socketId = waiter !== undefined ? waiter.socketID : null
            if(waiter_socketId === null){
                socket.emit('prepared_order_unavailable', 'Error while sending new prepared order to waiter');
            }else{
                io.to(waiter_socketId).emit('prepared_order_waiter', notification);
                notifications.addUserNotification(notification, waiter);
                socket.emit('prepared_order_sent', 'New prepared order notification sent');
            }
        }
    });

    //Notification from waiter to all cashiers with information about invoice
    socket.on('new_invoice', function(notification){
        if(notification != null){
            console.log('New invoice arrived, notifying all cashiers...');
            io.sockets.to(CASHIERS_CHANNEL).emit('new_invoice_cashiers', notification);
            notifications.addWorkerTypeNotification(notification, 'cashier');
            socket.emit('new_invoice_sent', 'New invoice notific');
        }
    });

    //Nofitication from cashier or waiter to all managers that a meal is terminated or that a previous terminated meal is now closed and considered as paid
    socket.on('meal_end', function(notification){
        if(notification != null){
            console.log('Meal terminated or paid, notifying all managers...');
            io.sockets.to(MANAGERS_CHANNEL).emit('meal_paid_managers', notification);
            notifications.addWorkerTypeNotification(notification, 'manager');
            socket.emit('meal_paid_sent', 'Meal terminated or paid notification sent');
        }
    });

    //Notification from worker to all managers with a problem
    socket.on('manager_situation', function(notification){
        if(notification != null){
            console.log('Sending notification to managers...');
            io.sockets.to(MANAGERS_CHANNEL).emit('manager_situation', notification);
            notifications.addWorkerTypeNotification(notification, 'manager');
            socket.emit('manager_situation_sent', 'Notification sent to managers');
        }
    });
});
