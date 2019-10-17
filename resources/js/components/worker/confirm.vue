<template>
	<div>
        <div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <div class="card rounded-0">
                        <div class="card-header">
                            <h3 class="mb-0">Password Definition</h3>
                        </div>
                        <div class="card-body">
                            <form class="form" role="form" autocomplete="off" id="formLogin" novalidate="" method="POST">
                                <div class="form-group">
                                    <label for="uname1">Password</label>
                                    <input type="password" class="form-control form-control-lg rounded-0" name="uname1" id="uname1" required="" v-model.trim="user.password">
                                </div>
                                <div class="form-group">
                                    <label>Password Confirmation</label>
                                    <input type="password" class="form-control form-control-lg rounded-0" id="pwd1" required="" autocomplete="new-password" v-model.trim="passwordConfirmation">
                                </div>
                                <button type="submit" class="btn btn-success btn-lg float-right" id="btnLogin" v-on:click.prevent="formValidateAndSend" :disabled="!formCompleted">Define Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
	</div>
</template>

<script type="text/javascript">
	export default {
		data:function(){
			return {
				user: {
					password:"",
				},
				passwordConfirmation:"",
			}
		},
		methods: {
			formValidateAndSend(){
				if(this.user.password == this.passwordConfirmation){
					axios.post(`api/register/activate/${this.$route.params.activationToken}`, this.user).then(response => {
						this.$toasted.success(`Account activated`, {
						position: "bottom-center",
		   				duration: 3000,
					});
						this.$router.push({name: 'menu'});
					}).catch(error => {
						this.$toasted.error(`Error activating account`, {
						position: "bottom-center",
		   				duration: 3000,
					});
						this.$router.push({name: 'menu'});
					});
				}else{
					this.$toasted.error(`Passwords don't match`, {
						position: "bottom-center",
		   				duration: 3000,
					});
				}
			}
		},
		computed: {
			formCompleted(){
        		return this.passwordConfirmation && this.user.password;
      		}
		},
		beforeRouteEnter(to, from, next){
			let token = to.params.activationToken;
			axios.get(`api/checkRegisted/${token}`).then(response =>{
				next();
			}).catch(error =>{
				return next("/");
			});
		}
	};
</script>