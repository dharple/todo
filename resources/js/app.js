$('select[name="edit_section_id"]').change(function () {
    $('input[name="edit_name"]').val($('select[name="edit_section_id"] option:selected').text());
});

//
// Experimental Chart.js support
//

var config = {
    type: 'doughnut',
    data: {
        datasets: [{
            backgroundColor: [
                '#52939D',
                '#395F80',
                '#DE8F4E',
                '#FED8B1',
                '#DFBD9A',
                '#D99057'
            ],
            label: 'Tasks Done By Week'
        }]
    },
    options: {
        responsive: false,
        legend: {
            display: false,
            position: 'top',
        },
        title: {
            display: false,
            text: 'Tasks Done By Week'
        },
        animation: {
            animateScale: false,
            animateRotate: false
        },
        circumference: Math.PI,
        rotation: -Math.PI
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
            return moment(row.start).format('MMM D');
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
});
