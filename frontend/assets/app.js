var app = {};

app.modals = [];

app.initilize = function () {
    app.initialize_templates();
    app.$users_list = $('#users-list');
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

app.initialize_form = function (user, cb) {

    var $modal = $(twig({ ref: "form" }).render({
        form_id: user ? 'edit_user_' + user.id : 'create-form',
        user: user
    }));

    _.each(app.modals, function ($modal, key) {
        $modal.remove();
        delete app.modals[key];
    });

    app.modals.push($modal);

    var $form = $modal.find('form'),
        $callerid = $form.find('[data-role="callerid"]'),
        $permit0 = $form.find('[data-role="permit0"]'),
        $permit1 = $form.find('[data-role="permit1"]'),
        $errors = $form.find('[data-role="errors"]');

    $errors.hide().html('');

    var callerid = $callerid.val().split('"');
    if (callerid.length > 1) callerid = callerid[1];
    else callerid = callerid[0];

    $callerid.val(callerid);

    $('body').append($modal);

    if (user && user.permit) {
        var permit = user.permit.split('/');
        $permit0.val(permit[0]);
        $permit1.val(permit[1]);
    }

    $permit0.ipAddress();
    $permit1.ipAddress();

    $form.submit(function(){
        $.ajax({
            url: 'post',
            type: 'post',
            data: $form.serialize(),
            success: function (data) {
                if (data.errors) {
                    $errors.html('');
                    _.each(data.errors, function (error) {
                        $errors.show().append($('<p>').addClass('text-error').text(error.field + ': ' + error.text));
                    });
                    return;
                }
                if (!data.success) return alert('error sending form');

                cb(data);
                $errors.hide().html('');
            }
        });

        return false;
    });

    return $modal;

};

app.initialize_row = function (user) {

    var $row = $(twig({ ref: "row" }).render(user)),
        $callerid = $row.find('[data-role="callerid"]'),
        $remove = $row.find('[data-role="remove"]'),
        $edit = $row.find('[data-role="edit"]');

    var callerid = $callerid.html().split('"');
    if (callerid.length > 1) callerid = callerid[1];
    else callerid = callerid[0];

    $callerid.text(callerid);

    $remove.click(function(){
        if (confirm('Are you sure?')) {
            $.ajax({
                url: 'remove',
                type: 'post',
                success: function(data) {
                    if (!data.success) return alert('error removing');
                    $row.fadeOut(500);
                }
            });
        }
        return false;
    });

    $edit.click(function(){
        var $modal;
        $modal = app.initialize_form(user, function (data) {
            var $new_row = app.initialize_row(data.user);
            $row.replaceWith($new_row);
            $modal.modal('hide');
        });
        $modal.modal('show');
        return false;
    });

    return $row;

};

app.initialize_create = function () {

    var $create = $('[data-role="create"]');

    $create.click(function(){
        var $modal;
        $modal = app.initialize_form(null, function (data) {
            var $row = app.initialize_row(data.user);
            app.$users_list.prepend($row);
            $modal.modal('hide');
        });
        $modal.modal('show');
        return false;
    });

};

$(document).ready(function(){

    app.initilize();

});