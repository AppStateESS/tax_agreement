var checkboxy = new Checkboxy;
$(window).load(function() {
    Pagers.callback = function() {
        checkboxy.bootRows();
    };
    checkboxy.bootCheckAll();
});

function Checkboxy() {
    t = this;
    this.check_all_box;
    this.all_rows;

    this.bootRows = function() {
        this.all_rows = $('.agreement-checkbox');
        $('.tax-info').popover({
            trigger: 'hover',
            html: true
        });

        $('.tax-delete').click(function() {
            var dicon = $(this);
            if (confirm('Are you sure you want to delete this agreement?')) {
                $.post('./tax_agreement/admin/',
                        {
                            action: 'delete',
                            id: dicon.data('id')
                        }).
                        success(function()
                        {
                            var row = dicon.parents('tr');
                            row.hide('slow', function() {
                                Pagers.reload('agreement-list');
                            });
                        });
            }
        });

        $('.tax-approve').click(function() {
            var dicon = $(this);
            $.post('./tax_agreement/admin/',
                    {
                        action: 'approve',
                        id: dicon.data('id')
                    }).
                    success(function()
                    {
                        var row = dicon.parents('tr');
                        row.hide('slow', function() {
                            Pagers.reload('agreement-list');
                        });
                    });
        });

        $('.tax-unapprove').click(function() {
            var dicon = $(this);
            $.post('./tax_agreement/admin/',
                    {
                        action: 'unapprove',
                        id: dicon.data('id')
                    }).
                    success(function()
                    {
                        var row = dicon.parents('tr');
                        row.hide('slow', function() {
                            Pagers.reload('agreement-list');
                        });
                    });
        });
    };

    this.bootCheckAll = function() {
        this.check_all_box = $('#check-all-box');
        this.check_all_box.click(function() {
            var status = $(this).prop('checked');
            t.all_rows.each(function(i, v) {
                $(v).prop('checked', status);
            });
        });
    };
}