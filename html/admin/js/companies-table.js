//== Class definition
var userId;

var options = {
    data: {
        type: 'remote',
        source: {
            read: {
                url: 'http://localhost:8080/api/allCompanies',
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
            width: 120,
        },
        {
            field: "phone_number",
            title: "Phone Number",
            width: 150,
            template: function(row){
                return '+'+row['phonecode']+' '+row['phone_number'];
            }
        },
        {
            field: "email_address",
            title: "Email Address",
            width: 150,
        },
        {
            field: "nicename",
            title: "Country",
            width: 100,
        },
        {
            field: "Actions",
            width: 110,
            title: "Actions",
            sortable: false,
            overflow: 'visible',
            template: function (row, index, datatable) {
                var dropup = (datatable.getPageSize() - index) <= 4 ? 'dropup' : '';
                return '\
                    <a href="http://localhost:8080/admin/view_company/'+ row['id'] +'" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View User">\
                        <i class="la la-eye"></i>\
                    </a>\
                    <button type="button" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="modal" data-target="#m_modal_1" onClick="getId(this)" data-id="'+row['id']+'"><i class="la la-trash"></i></button>\
                ';
            },
        }],

};
var datatable = $('#companiesTable').mDatatable(options);

function getId(id){
    var userId = id.getAttribute('data-id');    
    alert(userId);
    $('#btn-delete').attr('href', function(){
        return 'http://localhost:8080/api/delete_company/'+userId;
    });
};