//== Class definition

var options = {
    data: {
        type: 'remote',
        source: {
            read: {
                url: 'http://localhost:8080/api/userProfile/',
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
            field: "name",
            title: "Name",
            width: 150,
            template: function(row){
                return row['first_name'] +' '+ row['middle_name'] +' '+ row['last_name'];
            }
        },
        {
            field: "phone_number",
            title: "Phone Number",
            width: 120,
            template: function(row){
                return '+'+row['phonecode']+'-'+row['phone_number'];
            }
        },
        {
            field: "email_address",
            title: "Email Address",
            width: 180,
        },
        {
            field: "user_type",
            title: "User Type",
            width: 100,
            template: function(row){
                var status = {
                    1: {'title': 'Admin'},
                    2: {'title': 'Agent'},
                    3: {'title': 'Customer'},
                    0: {'title': 'Not Specified'},
                };
                return '<span class="m-badge '+ status[row.user_type].class +'m-badge--wide">'+ status[row.user_type].title +'</span>';
            }
        },
        {
            field: "nicename",
            title: "Country",
            width: 120,
        },
    ],

};
var datatable = $('#usersTable').mDatatable(options);






