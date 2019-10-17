<template>
  <div>
    <nav class="nav nav-tabs">
      <li class="nav-item">
        <router-link class="nav-link" active-class="active" to="/" exact>Menu</router-link>
      </li>
        <li class="nav-item" v-if="isManager">
            <router-link class="nav-link" active-class="active" to="/manager/dashboard" exact>Dashboard</router-link>
        </li>
      <li class="nav-item" v-if="isManager">
        <router-link class="nav-link" active-class="active" to="/manager/users" exact>Workers</router-link>
      </li>
        <li class="nav-item" v-if="isManager">
            <router-link class="nav-link" active-class="active" to="/manager/tables" exact>Tables</router-link>
        </li>
        <li class="nav-item" v-if="isManager">
            <router-link class="nav-link" active-class="active" to="/manager/meals" exact>All Meals</router-link>
        </li>
        <li class="nav-item" v-if="isWaiter">
            <router-link class="nav-link" active-class="active" to="/waiter/mymeals" exact>My Meals</router-link>
        </li>
        <li class="nav-item" v-if="isWaiter">
            <router-link class="nav-link" active-class="active" to="/waiter/orders" exact>My Orders</router-link>
        </li>
        <li class="nav-item" v-if="isCashier">
            <router-link class="nav-link" active-class="active" to="/invoices/pending" exact>Pending Invoices</router-link>
        </li>
        <li class="nav-item" v-if="isCashier">
            <router-link class="nav-link" active-class="active" to="/invoices/all" exact>All Invoices</router-link>
        </li>
        <li class="nav-item" v-if="isCook">
            <router-link class="nav-link" active-class="active" to="/cook/orders" exact>Orders</router-link>
        </li>
      <div v-if="this.$store.state.user">
        <li v-if="this.$store.state.user.last_shift_start && shiftStarted" class="nav-item" style="position: absolute; right:50%">
          <div class="row justify-content-md-center">
            <small><b>Shift time:</b> {{hours}}:{{minutes}}:{{seconds}}</small>
          </div>
          <div class="row justify-content-md-center">
            <small><b>This shift started at:</b> {{shiftStartedTime}}</small>
          </div>
        </li>
        <li v-if="this.$store.state.user.last_shift_end && !shiftStarted" class="nav-item" style="position: absolute; right:50%">
          <div class="row justify-content-md-center">
            <small><b>Not working for:</b> {{hours}}:{{minutes}}:{{seconds}}</small>
          </div>
          <div class="row justify-content-md-center">
            <small><b>Last shift:</b> {{shiftEndedTime}}</small>
          </div>
        </li>
      </div>
      <ul class="navbar-nav ml-auto">
        <span class="navbar-text" v-if="this.$store.state.user && shiftStarted">Currently Working</span>
        <span class="navbar-text" v-if="this.$store.state.user && !shiftStarted">Not Working</span>
        <li class="nav-item" v-if="!this.$store.state.user">
          <button type="button" class="btn btn-info navbar-btn" data-toggle="modal" data-target="#loginModal">
          Login
          </button>
        </li>
        <li class="nav-item" v-if="this.$store.state.user">
          <button class="nav-link btn btn-primary btn-md navbar-btn" v-if="!this.$store.state.user.shift_active" v-on:click.prevent="startShift" v-model="startShift">Start Shift</button>
          <button type="button" class="nav-link btn btn-primary btn-md navbar-btn" v-if="this.$store.state.user.shift_active" v-on:click="endShift">End Shift</button>
        </li>
        <li class="nav-item dropdown" v-if="this.$store.state.user">
          <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle" id="dropdownMenuLink" role="button" aria-haspopup="true" aria-expanded="false"><i class="material-icons md-18">person</i> {{this.$store.state.user.name}}<b class="caret"></b></a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
            <router-link class="dropdown-item" active-class="active" to="/profile" exact>Overview</router-link>
            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#changePassModal">Change Password</a>
            <router-link class="dropdown-item" active-class="active" data-count="2" to="/notifications" exact>Notifications</router-link>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">Logout</a>
          </div>
        </li>
      </ul>
    </nav>

    <!-- Login Modal -->
    <login></login>

    <!-- Logout Modal -->
    <logout></logout>

    <!-- Change Password Modal -->
    <changePassword></changePassword>

  </div>
</template>

<style>
  .material-icons.md-18 { font-size: 16px; }
  .navbar-nav{
    -webkit-flex-direction: row;
    -ms-flex-direction: row;
    flex-direction: row;
  }
  .h4 span{
    margin-right: 5px;
  }
  .btn-static {
    background-color: white;
    border: none;
  }
  .btn-static:active {
    -moz-box-shadow:    inset 0 0 0px white;
    -webkit-box-shadow: inset 0 0 0px white;
    box-shadow:         inset 0 0 0px white;
  }
  .navbar-nav > span{
    padding-right: 30px;
  }
  .navbar-nav > button{
    padding-right: 20px;
  }
</style>

<script type="text/javascript">
  import login from './worker/login.vue';
  import logout from './worker/logout.vue';
  import changePassword from './worker/changePassword.vue';

	export default {
    components: {
      'login' : login,
      'logout' : logout,
      'changePassword' : changePassword,
    },
    data: function(){
      return {
        timer: null,
        totalTime: 0,
        shiftStarted: false,
        shiftStartedTime: null,
        shiftEndedTime: null,
      }
    },
    methods: {
      startShift(){
        this.shiftStartedTime = this.formatDateTime(new Date());
        console.log('Shift started at ' + this.shiftStartedTime);
        axios.post('api/shift', {shiftStartedTime: this.shiftStartedTime}).then(response => {
          console.log(response.data.data);
          clearInterval(this.timer);
          this.totalTime = 0;
          this.timer = setInterval(() => this.stopWatch(), 1000);
          this.shiftStarted = true;
          this.$store.commit('setUser', response.data.data);
          this.$socket.emit('shift_started', this.$store.state.user);
          this.toastPopUp("success", "Shift Started");
        }).catch(error => {

        });
      },
      endShift(){
        this.shiftEndedTime = this.formatDateTime(new Date());
        console.log('Shift ended at ' + this.shiftEndedTime);
        axios.post('api/shift', {shiftEndedTime: this.shiftEndedTime}).then(response => {
          console.log(response.data.data);
          clearInterval(this.timer);
          this.totalTime = 0;
          this.timer = setInterval(() => this.stopWatch(), 1000);
          this.shiftStarted = false;
          this.$store.commit('setUser', response.data.data);
          this.$socket.emit('shift_ended', this.$store.state.user);
          this.toastPopUp("success", "Shift Ended");
        }).catch(error => {
          this.toastPopUp("error", "Error while ending shift");
        });
      },
      stopWatch(){
        this.totalTime++;
      },
      padTime(time){
        return (time < 10 ? '0' : '') + time;
      },
      formatDateTime(date){
        return (date.getFullYear() < 10 ? '0' + date.getFullYear() : date.getFullYear()) + '-' + ((date.getMonth()+1) < 10 ? '0' + (date.getMonth()+1): (date.getMonth()+1)) + '-' + (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' ' + (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':' + (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes()) + ':' + (date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds());
      },
      updateTimers(){
        if(this.$store.state.user){
          if(this.shiftStarted == false && this.timer == null){
            if(this.$store.state.user.shift_active == 1){
              let startTimeSec = new Date(this.$store.state.user.last_shift_start).getTime() / 1000;
              let nowTimeSec = new Date().getTime() / 1000;
              this.totalTime = Math.floor(nowTimeSec - startTimeSec);
              this.timer = setInterval(() => this.stopWatch(), 1000);
              this.shiftStarted = true;
              this.shiftStartedTime = this.$store.state.user.last_shift_start;
            }
            if(this.$store.state.user.shift_active == 0){
              let startTimeSec = new Date(this.$store.state.user.last_shift_end).getTime() / 1000;
              let nowTimeSec = new Date().getTime() / 1000;
              this.totalTime = Math.floor(nowTimeSec - startTimeSec);
              this.timer = setInterval(() => this.stopWatch(), 1000);
              this.shiftStarted = false;
              this.shiftEndedTime = this.$store.state.user.last_shift_end;
            }
          }
        }
      },
    },
    updated(){
      this.updateTimers();
    },
    mounted(){
      this.updateTimers();
    },
    computed: {
      isManager(){
        return this.$store.state.user && this.$store.state.user.type == "manager";
      },
      isWaiter(){
        return this.$store.state.user && this.$store.state.user.type == "waiter";
      },
      isCashier(){
        return this.$store.state.user && this.$store.state.user.type == "cashier";
      },
      isCook(){
        return this.$store.state.user && this.$store.state.user.type == "cook";
      },
      hours(){
        const hours = Math.floor(this.totalTime / 60 / 60);
        return this.padTime(hours);
      },
      minutes(){
        const minutes = Math.floor((this.totalTime % 3600) / 60);
        return this.padTime(minutes);
      },
      seconds(){
        const seconds = this.totalTime % 60;
        return this.padTime(seconds);
      },
    },
  }
</script>
