<template>
    <div>
        <navigation></navigation>
        <link href="css/profile.css" rel="stylesheet" type="text/css">
        <br>
        <div class="container emp-profile">
            <div class="row">
                <div class="col-md-4">
                    <div class="profile-img">
                        <img v-if="profileUser.photo_url != null" v-bind:src="getUserPhoto(profileUser.photo_url)" alt=""/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="profile-head">
                        <h5>
                            {{profileUser.name}}
                        </h5>
                        <h6>
                            {{profileUser.type.charAt(0).toUpperCase() + profileUser.type.slice(1)}}
                        </h6>
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">About</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="profile-edit-btn" name="btnAddMore" href="#editProfileModal" data-toggle="modal" data-target="#editProfileModal">Edit Profile</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="profile-work">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="tab-content profile-tab" id="myTabContent">
                        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Username</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{profileUser.username}}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Name</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{profileUser.name}}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Email</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{profileUser.email}}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Profession</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{profileUser.type.charAt(0).toUpperCase() + profileUser.type.slice(1)}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>       
        </div>
        <edit-profile @user-saved="getInformationFromLoggedUser"></edit-profile>
    </div> 
</template>
<script type="text/javascript">
    import navigation from '../navigation.vue';
    import editProfile from './editProfile.vue';   
    export default {
        components: {
            'navigation': navigation,
            'edit-profile': editProfile,
        },
        data: function(){
            return { 
                profileUser: null,
                successMessage: "",
                showSuccess: false
            }
        },
        methods: {
            getInformationFromLoggedUser() {
                this.profileUser = this.$store.state.user;
            },
            savedUser: function(){
                this.showSuccess = true;
                this.successMessage = "User's Profile Updated";
            },
            cancelEdit: function(){
                this.showSuccess = false;
            },     
        },
        mounted() {
            this.getInformationFromLoggedUser();
        }        
    }
</script>