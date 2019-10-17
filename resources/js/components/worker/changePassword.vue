<template>
	<div>
	<!-- Modal Change Password-->
	    <div class="modal fade" id="changePassModal" tabindex="-1" role="dialog" aria-labelledby="changePassModal" aria-hidden="true">
	      <div class="modal-dialog" role="document">
	        <div class="modal-content">
	          <div class="modal-header">
	            <h5 class="modal-title" id="changePassModal">Change Password</h5>
	            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	              <span aria-hidden="true">&times;</span>
	            </button>
	          </div>
	          <div class="modal-body">
	            <div class="card rounded-0">
	              <div class="card-body">
	                  <form class="form" role="form" autocomplete="off" id="formLogin" novalidate="" method="POST" data-toggle="validator">
	                    <div class="form-group">
	                          <label for="password" class="control-label">Current Password</label>
	                          <input type="password" class="form-control rounded-0" name="password" id="password" data-minlength="3" required v-model.trim="currentPassword">
	                      </div>
	                      <div class="form-group">
	                          <label for="newPassword" class="control-label">New Password</label>
	                          <input type="password" class="form-control rounded-0" name="newPassword" id="newPassword" data-minlength="3" required v-model.trim="newPassword">
	                          <div class="help-block">Minimum of 3 characters</div>
	                          <br>
	                      </div>
	                      <div class="form-group">
	                          <label for="passwordConfirmation" class="control-label">New Password Confirmation</label>
	                          <input type="password" class="form-control rounded-0" id="passwordConfirmation" required data-match="#newPassword" data-match-error="Passwords don't match" v-model.trim="passwordConfirmation">
	                          <div class="help-block with-errors"></div>
	                      </div>
	                      <button type="submit" class="btn btn-success btn-lg float-right" id="btnLogin" v-on:click.prevent="changePassword" :disabled="!passwordFormCompleted">Save</button>
	                  </form>
	              </div>
	            </div>
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
	        currentPassword: "",
	        newPassword: "",
	        passwordConfirmation: "",
	      }
    	},
    	methods: {
    		changePassword(){
		        axios.post('api/changePassword', {
		          email: this.$store.state.user.email,
		          curPassword: this.currentPassword,
		          newPassword: this.newPassword,
		        }).then(response => {
		          this.toastPopUp("success", response.data.message);
		          $('#changePassModal').modal('hide');
		          this.currentPassword = "";
		          this.newPassword = "";
		          this.passwordConfirmation = "";
		        }).catch(error => {
		          this.toastPopUp("error", `${error.response.data.message} - Error changing password`);
		          this.currentPassword = "";
		          this.newPassword = "";
		          this.passwordConfirmation = "";
		        });
		    },
    	},
    	computed: {
    		passwordFormCompleted(){
        		return this.currentPassword && this.newPassword && this.passwordConfirmation && this.newPassword == this.passwordConfirmation;
      		},
    	},
	}
</script>