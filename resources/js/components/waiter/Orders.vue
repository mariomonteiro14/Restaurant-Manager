<template>
    <div>
        <navigation></navigation>
        <br/>
        <div>
            <list-orders :prepared=false></list-orders>
            <list-orders :prepared=true></list-orders>
        </div>

    </div>
</template>

<script type="text/javascript">
    import navigation from '../navigation.vue';
    import orderList from './OrderList.vue';

    export default {
        components: {
            'navigation': navigation,
            'list-orders': orderList,
        },
        data: function () {
            return {
                title: "List Orders",
            }
        },
    }
</script>
