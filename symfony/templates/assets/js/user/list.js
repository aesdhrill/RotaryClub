import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css'

require('datatables.net-bs5/js/dataTables.bootstrap5.min')

$('#user_list').DataTable({
  serverSide: true,
  processing: true,
  ajax: dtAjaxPath,
  autoWidth: true,
  // language: {
  //   url: '/build/languages/datatables.' + locale + '.json',
  // },
  columns: [
    {
      data: 'index',
      orderable: false,
    },
    { data: 'fullname' },
    { data: 'email' },
    // { data: 'phone' },
    // {
    //   data: 'facilities',
    //   render: function (data, type) {
    //     return data.join(',<br>');
    //   },
    // },
    {
      data: 'roles',
      orderable: false,
      render: function (data, type) {
        return data.join(',<br>');
      },
    },
    {
      data: 'status',
      render: function (data, type) {
        return userStatusBadges[data];
      },
      className: "text-center",
    },
    {
      data: 'actionPaths',
      render: function (data, type) {
        return '<a class="btn btn-primary btn-sm" href="' + data['show'] + '"><i class="fas fa-eye"></i></a>';
        // return '<a class="btn btn-primary" href="' + data['edit'] + '"><i class="fas fa-pen"></i></a>';
      },
      orderable: false,
      className: 'text-center',
    }
  ],
  order: [[ 1, 'asc' ]],
  // drawCallback: function() {
  //     $(this).find('[data-bs-toggle="tooltip"]').each(function (_, ee) {
  //         return new bootstrap.Tooltip(ee)
  //     });
  // }
});
