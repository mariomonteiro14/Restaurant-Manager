<template>
	<div>
		<!-- Modal Notify Managers-->
	    <div class="modal fade" id="notifyManagers" tabindex="-1" role="dialog" aria-labelledby="notifyManagers" aria-hidden="true">
	      <div class="modal-dialog" role="document">
	        <div class="modal-content">
	          <div class="modal-header">
	            <h5 class="modal-title" id="notifyManagers">Notification</h5>
	            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	              <span aria-hidden="true">&times;</span>
	            </button>
	          </div>
	            <div class="modal-body">
	              <div class="container box">
	                <div class="form-group">
	                  <label id="emailLabel" for="inputSituation">Problem/Situation</label>
	                  <textarea class="form-control" rows="5" id="inputSituation" v-model="notification.message"></textarea>
	                </div>
	              </div>
	            </div>
	            <div class="modal-footer">
	              <button class="btn btn-info" v-on:click.prevent="sendNotification" :disabled="!formCompleted">Send</button>
	            </div>
	        </div>
	      </div>
	    </div>
	</div>
</template>

<script type="text/javascript">
	module.exports = {
		data: function(){
			return {
				notification: {
					from: this.$store.state.user.name,
					to: "all managers",
					message: "",
					url: "#/notifications",
					date: new Date().toUTCString(),
				},
			}
		},
		methods: {
			sendNotification(){
				this.$socket.emit("manager_situation", this.notification);
				this.$emit('notif-sent');
				$('#notifyManagers').modal('hide');
			}
		},
		computed: {
			formCompleted(){
				return this.notification.message;
			}
		},
	}
</script>