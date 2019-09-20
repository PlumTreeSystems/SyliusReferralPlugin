$('button[name="selectEnroller"]').on('click', function (e) {
    const element = $(e.target);
    const url = element.data('url');

    $('#selectEnrollerModal').modal('show');

    $('#selectEnrollerAgree').attr('href', url);
});