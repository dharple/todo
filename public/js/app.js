$('select[name="edit_section_id"]').change(function () {
    $('input[name="edit_name"]').val($('select[name="edit_section_id"] option:selected').text());
});

//
// Chart.js support
//
// Color Palette
//
// - URL: https://lospec.com/palette-list/into-blinding-tenebris
// - Author: https://lospec.com/dorian-termini
//

var config = {
    type: 'doughnut',
    data: {
        datasets: [{
            backgroundColor: [
                '#ffa300',
                '#e93841',
                '#7d3ebf',
                '#0000aa',
            ],
            label: 'Tasks Done'
        }]
    },
    options: {
        responsive: false,
        plugins: {
            legend: {
                display: false,
                position: 'top',
            },
            title: {
                display: false,
                text: 'Tasks Done By Week'
            },
        },
        animation: {
            animateScale: false,
            animateRotate: false
        },
        circumference: 180,
        rotation: 270,
        aspectRatio: 2
    }
};

$(document).ready(function() {
    //
    // Charts
    //

    if ($('#tasksByWeek').length) {
        //
        // Import chart data from page and render two copies of the chart, one for
        // the index page and one for the printed view.
        //

        config.data.datasets[0].data = $.map(chartData, function(row, i) {
            return row.done;
        });
        config.data.labels = $.map(chartData, function(row, i) {
            return row.weekOf;
        });

        let noPrintConfig = $.extend({}, config);
        let ctx = document.getElementById('tasksByWeek').getContext('2d');
        window.myDoughnut = new Chart(ctx, noPrintConfig);

        let printConfig = $.extend({}, config);
        printConfig.data.datasets[0].backgroundColor = '#ffffff';
        printConfig.data.datasets[0].borderColor = '#000000';
        ctx = document.getElementById('tasksByWeek2').getContext('2d');
        window.myDoughnut2 = new Chart(ctx, printConfig);
    }

    $('#select-all').on('click', function() {
        $('input[type="checkbox"]').attr('checked', 'checked');
    });

    $('#select-none').on('click', function() {
        $('input[type="checkbox"]').attr('checked', null);
    });
});
