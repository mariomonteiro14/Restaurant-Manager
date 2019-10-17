<template>
    <div>
        <navigation></navigation>
        <br>
        <div class="jumbotron">
            <h1>{{ title }}</h1>
        </div>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addWorkerModal">New worker</button>
        <b-pagination align="right" :total-rows="pagination.total" :current="pagination.current_page" :per-page="pagination.per_page" :simple="false" order="is-right" @change="nextPage"></b-pagination>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Blocked</th>
                    <th>Shift Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users" :key="user.id">
                    <td>
                       <img width="50" height="50" v-if="user.photo_url != null"v-bind:src="getUserPhoto(user.photo_url)"/>
                    </td>
                    <td>{{ user.username }}</td>
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>
                    <td>{{ user.type }}</td>
                    <td v-if="isManager && user.id != getAuthUser.id">
                        <a class="btn btn-success" v-if="user.blocked == 1" @click="updateBlockedStatus(user, 0)">unblock</a>
                        <a class="btn btn-danger" v-else @click="updateBlockedStatus(user, 1)">block</a>
                    </td>
                    <td v-else>{{ getBlocked(user.blocked) }}</td>
                    <td>{{ getStatus(user.shift_active) }}</td>
                    <td v-if="isManager && user.id != getAuthUser.id"><a class="btn btn-danger" @click="deleteUser(user)">Delete</a></td>
                </tr>
            </tbody>
        </table>


        <!-- Modal Add Worker-->
    <div class="modal fade" id="addWorkerModal" tabindex="-1" role="dialog" aria-labelledby="addWorkerModal" aria-hidden="true" v-if="this.$store.state.user">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addWorkerModal">New Worker</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
            <div class="modal-body">
              <div class="container box">
                <div class="form-group">
                  <label id="nameLabel" for="inputName">Name</label>
                  <input
                      type="text" class="form-control" v-model.trim="user.name"
                      name="inputName" id="inputName"
                      placeholder="Worker's name"/>
                </div>
                <div class="form-group">
                  <label id="usernameLabel" for="inputUsername">Username</label>
                  <input
                      type="text" class="form-control" v-model.trim="user.username"
                      name="inputUsername" id="inputUsername"
                      placeholder="Worker's username"/>
                </div>
                <div class="form-group">
                  <label id="emailLabel" for="inputEmail">Email</label>
                  <input
                      type="email" class="form-control" v-model.trim="user.email"
                      name="inputEmail" id="inputEmail"
                      placeholder="Worker's email"/>
                </div>
                <label id="typeLabel">Type</label>
                <div class="form-group">
                    <input
                      type="radio" class="radio" value="manager" v-model.trim="user.type"
                      id="manager"/>
                    <label for="manager">Manager</label>
                    <input
                      type="radio" class="radio" value="cook" v-model.trim="user.type"
                      id="cook"/>
                    <label for="cook">Cook</label>
                    <input
                      type="radio" class="radio" value="waiter" v-model.trim="user.type"
                      id="waiter"/>
                    <label for="waiter">Waiter</label>
                    <input
                      type="radio" class="radio" value="cashier" v-model.trim="user.type"
                      id="cashier"/>
                    <label for="cashier">Cashier</label>
                </div>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="image" v-on:change="handleFile">
                    <label class="custom-file-label" for="image">Worker's Profile Picture</label>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-info" v-on:click.prevent="addWorker" :disabled="!formCompleted">Add worker</button>
            </div>
        </div>
      </div>
    </div>
    </div>
</template>

<script type="text/javascript">
    import navigation from '../navigation.vue';
	export default {
        components:{
            'navigation': navigation,
        },
        data:function() {
            return {
                title: "List Workers",
                users: [],
                user: {
                    name: "",
                    username: "",
                    email: "",
                    type: "",
                    photo: "",
                },
                imageOk: true,
                pagination: {},
            }
        },
        methods: {
            getUsers(url){
                if(url == null){
                    url = this.pagination == null ? 'api/users' : `/api/users?page=${this.pagination.current_page}`;
                }
                axios.get(url).then(response=>{
                    this.makePagination(response.data);
                    this.users = response.data.data;
                });
            },
            makePagination(data){
                let pagination = {
                    total: data.total,
                    current_page: data.current_page,
                    per_page: data.per_page,
                    last_page: data.last_page,
                    next_page_url: data.next_page_url,
                    prev_page_url: data.prev_page_url
                }
               this.pagination = pagination;
               console.log(pagination);
            },
            nextPage(pageNum){
                let url = '/api/users?page=';
                url += pageNum;
                return this.getUsers(url);
            },
            getBlocked(value){
                return value == 1 ? "True" : "False";
            },
            getStatus(value){
                return value == 1 ? "Working" : "Not Working"
            },
            updateBlockedStatus(user, status){
                axios.put('api/users/'+user.id, {blocked: status})
                    .then(response => {
                        user.blocked=status;
                        this.toastPopUp("success", "User Updated");
                    }).catch(error => {
                        console.log(error);
                        this.$toasted.error(`${error.message} - Error while updating user`);
                    });
            },
            deleteUser(user){
                axios.delete('api/users/'+user.id)
                    .then(response => {
                        this.toastPopUp("success", "User Deleted");
                        this.getUsers();
                    }).catch(error => {
                    console.log(error);
                    this.$toasted.error(`${error.message} - Error while deleting user`);
                });
            },
            addWorker(){
                let formData = this.formCreate();
                axios.post('api/register', formData).then(response =>{
                    console.log(response.data);
                    this.$toasted.success('Worker added', {
                        position: "bottom-center",
                        duration: 3000,
                    });
                    $('#addWorkerModal').modal('hide');
                }).catch(error => {
                    console.log(error);
                    this.$toasted.error(`${error.message} - Error while adding new worker`, {
                        position: "bottom-center",
                        duration: 3000,
                    });
                });
            },
            handleFile(e){
                var files = e.target.files || e.dataTransfer.files;
                if(files.length != 1){
                    this.$toasted.error('Only one file allowed', {
                        position: "bottom-center",
                        duration: 3000,
                    });
                    this.imageOk = false;
                    return;
                }
                if (!files[0].type.includes("image/")) {
                    this.$toasted.error('File must be an image', {
                        position: "bottom-center",
                        duration: 3000,
                    });
                    this.imageOk = false;
                    return;
                }
                this.imageOk = true;
                this.user.photo = files[0];
            },
            formCreate(){
                let formData = new FormData();
                formData.append('name', this.user.name);
                formData.append('username', this.user.username);
                formData.append('email', this.user.email);
                formData.append('type', this.user.type);
                formData.append('photo', this.user.photo);
                return formData;
            }
        },
        mounted(){
            this.getUsers();
        },
        computed: {
            formCompleted(){
                return this.user.name && this.user.username && this.user.email && this.user.type && this.imageOk;
            },
            isManager(){
                return this.$store.state.user && this.$store.state.user.type == "manager";
            },

        },
	};
</script>
