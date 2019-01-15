//== Class definition

var options = {
    data: {
        type: 'remote',
        source: {
            read: {
                url: 'http://localhost:8080/api/getAgentPackages/'.$agentId,
                method: 'GET',
                params: {
                    // custom parameters
                },
                map: function(raw){
                    // sample data mapping
                    var dataset = raw;
                    if(typeof raw.data !== 'undefined'){
                        dataset = raw.data;
                    }
                    console.log(dataset);
                    return dataset;
                },
            },
        },
        pageSize: 10,
        servingPaging: false,
        serverFiltering: false,
        serverSorting: false,
        autoColumns: false
    },

    layout: {
        theme: 'default',
        class: 'm-datatable--brand',
        scroll: false,
        height: null,
        footer: false,
        header: true,
    },

    sortable: false,

    pagination: {
        // page size select
        pageSizeSelect: [10, 20, 30, 50, 100],
    },

    search: {
        // enable trigger search by keyup enter
        onEnter: false,
        // input text for search
        input: $('#generalSearch'),
        // search delay in milliseconds
        delay: 400,
    },

    columns: [
        {
            field: "id",
            title: "ID",
            sortable: false,
            width: 20,
        },
        {
            field: "title",
            title: "Name",
            width: 150,
        },
        {
            field: "package_category",
            title: "Category",
            width: 120,
        },
        {
            field: "valid_from",
            title: "Valid From",
            width: 180,
        },
        {
            field: "valid_to",
            title: "Valid To",
            width: 100,
        },
        {
            field: "location_city",
            title: "City",
            width: 100,
        },
        {
            field: "country",
            title: "Country",
            width: 120,
        },
        {
            field: "price",
            title: "Price ($)",
            width: 100,
        },
    ],

};
var datatable = $('#packagesTable').mDatatable(options);






