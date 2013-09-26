var app = {};

app.initilize = function () {
    app.initialize_templates();
    app.$users_list = $('#users-list');
    app.$create_form = $('#create-form');
    app.list();
    app.initialize_create();
};

app.initialize_templates = function () {
    app.templates = {};
    app.templates.form = twig({
        id: "form",
        href: "templates/form.twig",
        async: false
    });
    app.templates.row = twig({
        id: "row",
        href: "templates/row.twig",
        async: false
    });
};

app.list = function () {
    $.ajax({
        url: 'list',
        success: function(data) {
            if (!data.success) return alert('error loading list');
            _.each(data.list, function (user) {
                app.initialize_row(user);
            });
        }
    });
};

app.initialize_row = function (user) {

    var $row = $(twig({ ref: "row" }).render(user)),
        $remove = $row.find('[data-role="remove"]'),
        $edit = $row.find('[data-role="edit"]');

    app.$users_list.append($row);

    $remove.click(function(){
        $.ajax({
            url: 'remove',
            type: 'post',
            success: function(data) {
                if (!data.success) return alert('error removing');
                $row.fadeOut(500);
            }
        });
        return false;
    });

};

app.initialize_create = function () {

    app.$create_form.submit(function(){
        $.ajax({
            url: 'create',
            type: 'post',
            data: app.$create_form.serialize(),
            success: function (data) {
                if (data.errors) {
                    _.each(data.errors, function (error) {
                        alert(error.field + ': ' + error.text);
                    });
                    return;
                }
                if (!data.success) return alert('error creating');

                app.initialize_row(data.user);

                $('#create').modal('hide');
            }
        });

        return false;
    });

};

$(document).ready(function(){

    app.initilize();

});