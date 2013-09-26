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
                var $row = app.initialize_row(user);
                app.$users_list.append($row);
            });
        }
    });
};

app.initialize_row = function (user) {

    var $row = $(twig({ ref: "row" }).render(user)),
        $remove = $row.find('[data-role="remove"]'),
        $edit = $row.find('[data-role="edit"]');

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

    $edit.click(function(){

        var $edit_modal = $(twig({ ref: "form" }).render({
            form_id: 'edit_user_' + user.id,
            user: user
        }));

        var $edit_form = $edit_modal.find('form');

        $('body').append($edit_modal);

        $edit_form.submit(function(){
            $.ajax({
                url: 'post',
                type: 'post',
                data: $edit_form.serialize(),
                success: function (data) {
                    if (data.errors) {
                        _.each(data.errors, function (error) {
                            alert(error.field + ': ' + error.text);
                        });
                        return;
                    }
                    if (!data.success) return alert('error editing');

                    var $new_row = app.initialize_row(data.user);
                    $row.replaceWith($new_row);

                    $edit_modal.modal('hide');
                }
            });

            return false;
        });

        $edit_modal.modal('show');

        return false;
    });

    return $row;

};

app.initialize_create = function () {

    app.$create = $(twig({ ref: "form" }).render({
        form_id: 'create-form'
    }));

    app.$create.attr('id', 'create');

    app.$create_form = app.$create.find('form');

    $('body').append(app.$create);

    app.$create_form.submit(function(){
        $.ajax({
            url: 'post',
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

                var $row = app.initialize_row(data.user);
                app.$users_list.prepend($row);

                app.$create.modal('hide');
            }
        });

        return false;
    });

};

$(document).ready(function(){

    app.initilize();

});