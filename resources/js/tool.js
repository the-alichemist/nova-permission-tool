Nova.booting((Vue) => {
    Vue.component("index-role", require("./components/fields/role/IndexField").default);
    Vue.component("detail-role", require("./components/fields/role/DetailField").default);
    Vue.component("form-role", require("./components/fields/role/FormField").default);

    Vue.component("index-permission", require("./components/fields/permission/IndexField").default);
    Vue.component("detail-permission", require("./components/fields/permission/DetailField").default);
    Vue.component("form-permission", require("./components/fields/permission/FormField").default);
    
    Nova.inertia("PermissionTool", require("./components/Tool").default);
  });

  