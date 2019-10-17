<template>
	<div>
	<!-- Modal Edit Profile-->
	    <div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModal" aria-hidden="true">
	      <div class="modal-dialog" role="document">
	        <div class="modal-content">
	          <div class="modal-header">
	            <h5 class="modal-title" id="editProfileModal">Edit Profile</h5>
	            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	              <span aria-hidden="true">&times;</span>
	            </button>
	          </div>
	          	<div class="modal-body">
	              <div class="container box">
	                <div class="form-group">
	                  <label id="username" for="inputUsername">Username</label>
	                  <input
	                      type="text" class="form-control" v-model.trim="user.newUsername"
	                      name="username" id="inputUsername"
	                      placeholder="Username"/>
	                </div>
	                <div class="form-group">
	                  <label for="inputName">Name</label>
	                  <input
	                      type="text" class="form-control" v-model="user.newName"
	                      name="name" id="inputName"/>
	                </div>
	                <div class="form-group">
	                  <label for="inputPhoto">Profile Photo</label>
	                  <input
	                      type="file" class="form-control" v-on:change="handleFile"
	                      name="photo" id="inputPhoto"/>
	                </div>
	                <button type="button" id="removePhoto"class="btn btn-outline-secondary" v-on:click.prevent="removePhoto">Remove Profile Photo</button>
	              </div>
	            </div>
	            <div class="modal-footer">
	              <button class="btn btn-info" v-on:click.prevent="editProfile">Save</button>
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
				user: {
					username: "",
					newUsername: "",
					newName: "",
					newPhoto: "",
				},
			}
		},
		methods: {
			getUser(){
				this.user.username = this.$store.state.user.username;
				this.user.newUsername = this.$store.state.user.username;
				this.user.newName = this.$store.state.user.name;
				this.user.newPhoto = '';
			},
			removePhoto(){
				let remBtn = document.getElementById('removePhoto');
				if(!remBtn.classList.contains('active')){
					this.user.newPhoto = 'removePhoto';
					remBtn.classList.add('active');
				}else{
					this.user.newPhoto = '';
					document.getElementById('inputPhoto').value = null;
					remBtn.classList.remove('active');
				}
			},
			editProfile(){
				this.formCheck();
				const config = {
            		headers: { 'content-type': 'multipart/form-data' }
        		}
				axios.post('api/editProfile', this.formCreate(), config).then(response => {
					this.$store.commit('setUser', response.data.data);
					this.toastPopUp("success", "User successfully edited");
					this.getUser();
					this.$emit('user-saved');
					$('#editProfileModal').modal('hide');
					document.getElementById('inputPhoto').value = null;
					let remBtn = document.getElementById('removePhoto');
					if(remBtn.classList.contains('active')) remBtn.classList.remove('active');
				}).catch(error => {
					if(error.response){
						this.toastPopUp("error", error.response.data.message);
					}else{
						this.toastPopUp("error", "Error while editing user");
					}
				});
			},
			formCheck(){
				if(this.user.newUsername == this.user.username){
					this.user.newUsername = '';
				}
				if(this.user.newName == this.$store.state.user.name){
					this.user.newName = '';
				}
			},
			handleFile(e){
				var files = e.target.files || e.dataTransfer.files;
	            if(files.length != 1){
	                this.$toasted.error('Only one file allowed', {
	                    position: "bottom-center",
	                    duration: 3000,
	                });
	                return;
	            }
	            if (!files[0].type.includes("image/")) {
	                this.$toasted.error('File must be an image', {
	                    position: "bottom-center",
	                    duration: 3000,
	                });
	                return;
	            }
	            this.user.newPhoto = files[0];
			},
			formCreate(){
				let formData = new FormData();
				formData.append('username', this.user.username);
				formData.append('newName', this.user.newName);
				formData.append('newUsername', this.user.newUsername);
				formData.append('newPhoto', this.user.newPhoto);
				return formData;
			}
		},
		mounted(){
			this.getUser();
		},
	}
</script>