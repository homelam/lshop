// 注册一个名为 user-addresses-create-and-edit的组件
Vue.component('user-addresses-create-and-edit', {
    // 组件的数据
    data() {
        return {
            province: '', // 省
            city: '', // 市
            district: '', // 区
        }
    },
    methods: {
        onDistrictChanged(val) {
            if (val.length === 3) {
                this.province = val[0];
                this.city = val[1];
                this.district = val[2];
            }
        }
    }
});