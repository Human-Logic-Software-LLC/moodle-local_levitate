function separateByScormId(data, scormId) {
    return data.filter(courseDataGroup => courseDataGroup.some(courseData => courseData.scorm_remote_id === scormId));
}
function format(d) {

    return '<div id="chart_div_'+d.scorm_remote_id+'" style="position:relative; width: 800px; height: 400px;"></div>';
}
function updateDataTable(newData, rawDataJSON,courses_table) {
    creating_table.clear().draw();

    createDataTable('update', newData, rawDataJSON,courses_table);
}
function createDataTable(Y,data,rawDataJSON,courses_table) {

    temp_rawData = JSON.parse(data);
    data = JSON.parse(courses_table);
    data.forEach(function (courseitem) {
         temp_rawData.forEach(function (dataitem) {
           
            if(courseitem.scorm_remote_id == dataitem.scorm_remote_id){
                courseitem.enrolments=dataitem.y;
                courseitem.timespent=dataitem.timespent;
            }

         });
    });
    
    data.forEach(function (item) {
        item.timespent = Math.round(parseInt(item.timespent) / 60000);
        
        item.timespent = item.timespent+' mins';
    });
    rawDataJSON = JSON.parse(rawDataJSON);

    rawDataJSON = year_rawdata_json(rawDataJSON,$(".table_select").val());
 
    
    // Define the DataTable columns
    var columns = [
        {  data: 'label',title: 'Course Name' },
        {  data: 'enrolments', title: 'Enrolments' },
        // {  data: 'completions',title: 'Total Completions' },
        {  data: 'timespent', title: 'Total Time Spent' },
        {
        className: 'open_graph',
        data: null, // Use 'null' to reference the entire row data
        title: 'Graph',
        render: function (data, type, row) {
           if (parseInt(data.enrolments) > 0) {
                $(row).addClass('disable');
            }
            // Customize the rendering based on the rendering type
            if (type === 'display') {
                // Render the icon with the hidden value
                
                    return '<span class="icon opengraph" data-hidden-value="' + data.scorm_remote_id + '"><img style="width: 40px; height: 30px;" src=""></span>';
               
            } else {
                // For other types, just return the hidden value
                return data.timespent;
            }
        }
    }

    ];
    if(Y !== 'update'){
        $.noConflict();
        // Create the DataTable
       
        try {
            creating_table = $('#datatable').DataTable({
                data: data,
                columns: columns

            });
        }
        catch(err) {
        setTimeout(function () {
           creating_table = $('#datatable').DataTable({
                data: data,
                columns: columns

            });
        }, 10000);
        }
        
    }
    else{
        creating_table.rows.add(data).draw();
    }

    // Customize DataTable options if needed
    creating_table.order([0, 'asc']); // Sort by the first column in ascending order
    creating_table.on('click', 'span.opengraph', function (e) {
        let tr = e.target.closest('tr');
        let row = creating_table.row(tr);

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
        }
        else {
            // Open this row
            row.child(format(row.data())).show();
            drawGraphs(row.data().scorm_remote_id,rawDataJSON);
        }
    });
}

function drawGraphs(scormId,rawData) {

    for (let rdjson of rawData) {
        for (let rd of rdjson) {
            if(rd){
                rd.timecreated = parseInt(rd.timecreated);
                rd.completed_pages = parseInt(rd.completed_pages);
                rd.scorm_remote_id = parseInt(rd.scorm_remote_id);
                rd.timespent = parseInt(rd.timespent);
                rd.total_pages = parseInt(rd.total_pages);
                rd.user_id = parseInt(rd.user_id);
            }
        }
    }
    var filteredData = separateByScormId(rawData, parseInt(scormId));
    drawGraph(scormId, filteredData);

}
function roundNumber(number, decimal_digit) {
    let powerOften = Math.pow( 10, decimal_digit );
    let result = Math.round( number * powerOften ) / powerOften;
    return result;
 }
function drawGraph(scormId, data) {
  
    var allMonths = [
        "Jan", "Feb", "Mar", "Apr", "May", "Jun",
        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
    ];
    var margin = { top: 20, right: 50, bottom: 50, left: 50 };
    var width = 800 - margin.left - margin.right;
    var height = 400 - margin.top - margin.bottom;
    var legendPadding = 30;

    var svg = d3
        .select('#chart_div_' + scormId)
        .append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom + legendPadding)
        .append('g')
        .attr('transform', 'translate(' + margin.left + ',' + (margin.top + legendPadding) + ')');

    var parseTime = d3.timeParse('%b %Y');

    var monthlyData = {}; // Store sum of timespent for each month
    var enrollmentData = {}; // Store enrollment count for each month
    var completionData = {}; // Store completion count for each month

    data.forEach(function (courseDataGroup) {
        courseDataGroup.forEach(function (courseData) {
            var date = new Date(courseData.timecreated * 1000);
            var month = date.toLocaleString('en-us', { month: 'short' });
            var year = date.getFullYear();
            var formattedTime = month + ' ' + year;

            if (!monthlyData[formattedTime]) {
                monthlyData[formattedTime] = 0;
                enrollmentData[formattedTime] = 0;
                completionData[formattedTime] = 0;
            }

            monthlyData[formattedTime] += courseData.timespent;
            enrollmentData[formattedTime] += 1;
            if (courseData.total_pages === courseData.completed_pages) {
                completionData[formattedTime] += 1;
            }
        });
    });

    var chartData = Object.keys(monthlyData).map(function (key) {
        timespentval = monthlyData[key]/60000;
        timespentval =  roundNumber(timespentval,1)
        return {
            monthYear: key,
            timespent: timespentval,
            enrollments: enrollmentData[key],
            completions: completionData[key],
        };
    });
    function getSortableMonth(monthYear) {
        const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        const [month, year] = monthYear.split(" ");
        const monthIndex = monthNames.indexOf(month);
        return new Date(year, monthIndex).getTime();
    }
    
    // Sort the array based on the monthYear property
    chartData.sort(function (a, b) {
        const sortableA = getSortableMonth(a.monthYear);
        const sortableB = getSortableMonth(b.monthYear);
        return sortableA - sortableB;
    });
    


    var xscale = d3.scaleBand()
    .domain(allMonths) // Use allMonths array for x-axis domain
    .rangeRound([0, width])
    // .style("font-size", "14px")
    .padding(0.65);
    var y0 = d3.scaleLinear()
        .domain([0, d3.max(chartData, function (d) { return d.timespent; })])
        .nice()
        .range([height, 0]);

    var y1 = d3.scaleLinear()
        .domain([0, d3.max(chartData, function (d) { return Math.max(d.enrollments, d.completions); })])
        .nice()
        .range([height, 0]);

    var xAxis = d3.axisBottom(xscale);
    var yAxisLeft = d3.axisLeft(y0);
    var yAxisRight = d3.axisRight(y1);

    svg.append('g')
        .attr('class', 'x-axis')
        .attr('transform', 'translate(0,' + height + ')')
        .call(xAxis);

    svg.append('g')
        .attr('class', 'y-axis')
        .call(yAxisLeft);

    svg.append('g')
        .attr('class', 'y-axis')
        .attr('transform', 'translate(' + width + ',0)')
        .call(yAxisRight);
    
    var tooltip = d3.select("#chart_div_"+ scormId).append("div")
    .attr("class", "tooltip")
    .style("opacity", 0);

    // Add tooltips to bars
    svg.selectAll('.bar')
        .data(chartData)
        .enter()
        .append('rect')
        .attr('class', 'bar')
        .attr('x', function (d) {var parts = d.monthYear.split(" ");
        var shortenedMonthName = parts[0]; return xscale(shortenedMonthName); })
        .attr('y', function (d) { return y0(d.timespent); })
        .attr('width', 5)
        .attr('height', function (d) { return height - y0(d.timespent); })
        .attr("rx", 3) // Set the x-axis corner radius
        .attr("ry", 3) // Set the y-axis corner radius
        .style('fill', 'green')
        .on("mouseover", function (event, d) {
            var [x, y] = d3.mouse(this);
            tooltip.transition()
                .duration(200)
                .style("opacity", 0.9); 
            tooltip.html("Month: " + event.monthYear + "<br>Timespent: " + event.timespent+ "<br>Enrolments: " + event.enrollments+ "<br>Completions: " + event.completions)
                .style("left", (x + 75) + "px")
                .style("top", (y ) + "px");
        })
        .on("mousemove", function (event, d) {
            var [x, y] = d3.mouse(this);
            tooltip.style("left", (x + 75) + "px")
                .style("top", (y ) + "px");
        })
        .on("mouseout", function () {
            tooltip.transition()
                .duration(500)
                .style("opacity", 0);
        });


    // Add bars for completion count
svg.selectAll('.bar-completions')
    .data(chartData)
    .enter()
    .append('rect')
    .attr('class', 'bar-completions')
    // .attr("x", function (d, key) { return xScale(Object.keys(aggregatedData)[key].replace(' '+$(".completion_statistics").val(),'')) + xScale.bandwidth() / 3; }) // Shift x position for grouped bars

    .attr("x", function (d, key) { var parts = d.monthYear.split(" ");
        var shortenedMonthName = parts[0]; return xscale(shortenedMonthName)+ xscale.bandwidth() / 2; })
    .attr('y', function (d) { return y1(d.completions); })
    .attr('width', 5)
    .attr('height', function (d) { return height - y1(d.completions); })
    .attr("rx", 3) // Set the x-axis corner radius
    .attr("ry", 3) // Set the y-axis corner radius
    .style('fill', '#FBC02D')
    .on("mouseover", function (event, d) {
        var [x, y] = d3.mouse(this);
        tooltip.transition()
            .duration(200)
            .style("opacity", 0.9); 
        tooltip.html("Month: " + event.monthYear + "<br>Timespent: " + event.timespent+ "<br>Enrolments: " + event.enrollments+ "<br>Completions: " + event.completions)
            .style("left", (x + 75) + "px")
            .style("top", (y ) + "px");
    })
    .on("mousemove", function (event, d) {
        var [x, y] = d3.mouse(this);
        tooltip.style("left", (x + 75) + "px")
            .style("top", (y ) + "px");
    })
    .on("mouseout", function () {
        tooltip.transition()
            .duration(500)
            .style("opacity", 0);
    });

// Add bars for enrollment count
svg.selectAll('.bar-enrollments')
    .data(chartData)
    .enter()
    .append('rect')
    .attr('class', 'bar-enrollments')
    .attr('x', function (d) { var parts = d.monthYear.split(" ");
        var shortenedMonthName = parts[0]; return xscale(shortenedMonthName)+ xscale.bandwidth(); })
    .attr('y', function (d) { return y1(d.enrollments); })
    .attr('width', 5)
    .attr('height', function (d) { return height - y1(d.enrollments); })
    .attr("rx", 3) // Set the x-axis corner radius
    .attr("ry", 3) // Set the y-axis corner radius
    .style('fill', '#4050E7')
    .on("mouseover", function (event, d) {
        var [x, y] = d3.mouse(this);
        tooltip.transition()
            .duration(200)
            .style("opacity", 0.9); 
        tooltip.html("Month: " + event.monthYear + "<br>Timespent: " + event.timespent+ "<br>Enrolments: " + event.enrollments+ "<br>Completions: " + event.completions)
            .style("left", (x + 75) + "px")
            .style("top", (y ) + "px");
    })
    .on("mousemove", function (event, d) {
        var [x, y] = d3.mouse(this);
        tooltip.style("left", (x + 75) + "px")
            .style("top", (y ) + "px");
    })
    .on("mouseout", function () {
        tooltip.transition()
            .duration(500)
            .style("opacity", 0);
    });

// Create a legend
var legend = svg.append('g')
    .attr('class', 'legend')
    .attr('transform', 'translate(' + (width - 150) + ',' + -legendPadding + ')');// Adjust the coordinates as needed

// Legend for Completions
legend.append('circle')
    .attr('cx', -140)
    .attr('cy', 5)
    .attr('r', 5)
    .style('fill', '#FBC02D');

legend.append('text')
    .attr('x', -130)
    .attr('y', 8)
    .text('Completions');

// Legend for Enrollments
legend.append('circle')
    .attr('cx', -25) // Adjust the distance between legend items
    .attr('cy', 5)
    .attr('r', 5)
    .style('fill', '#4050E7');

legend.append('text')
    .attr('x', -15) // Adjust the distance between legend items
    .attr('y', 8)
    .text('Enrollments');

// Legend for Timespent
legend.append('circle')
    .attr('cx', 90)
    .attr('cy', 5)
    .attr('r', 5)
    .style('fill', 'green');

legend.append('text')
    .attr('x', 100)
    .attr('y', 8)
    .text('Timespent');



    
        

}
 
function createallcourseschart(aggregatedData){
    
    var timespent_data = [];
    var enrollment_data = [];
    var completion_data = [];

    Object.keys(aggregatedData).forEach(function(course) {
        var tempTimespent = {};
        tempTimespent.x = parseInt(aggregatedData[course].timestamp + '000');
        tempTimespent.y = parseInt(aggregatedData[course].timespent);
        timespent_data.push(tempTimespent);
    
        var tempEnrollment = {};
        tempEnrollment.x = parseInt(aggregatedData[course].timestamp + '000');
        tempEnrollment.y = parseInt(aggregatedData[course].enrollments);
        enrollment_data.push(tempEnrollment);
    
        var tempCompletion = {};
        tempCompletion.x = parseInt(aggregatedData[course].timestamp + '000');
        tempCompletion.y = parseInt(aggregatedData[course].completions);
        completion_data.push(tempCompletion);
    });
    var options = {
        title: {
            text: "Column Chart in jQuery CanvasJS"              
        },
        axisYType: "secondary",
        data: [              
        {
            // Change type to "doughnut", "line", "splineArea", etc.
            type: "column",
            name: "Portal  Entrada",
            xValueType: "dateTime",
            showInLegend: true,
            dataPoints: timespent_data
        },
        {
			type: "line",
			name: "Expected Sales",
            xValueType: "dateTime",
			showInLegend: true,
            axisYType: "secondary",
			dataPoints: enrollment_data
		},
        {
			type: "area",
			name: "Profit",
			markerBorderColor: "white",
			markerBorderThickness: 2,
			xValueType: "dateTime",
			showInLegend: true,
            axisYType: "secondary",
			dataPoints: completion_data
		}
        ]
    };
    
    $("#chartContainer").CanvasJSChart(options);
    
    
}
function create(Y,tabledata,rawData,allcourses,aggregatedData){ 

// google.charts.load('current', {'packages':['corechart']});
//                 google.charts.setOnLoadCallback(function() {
//                 var tableData = $.parseJSON(tabledata);
//                 var rawDataJSON = $.parseJSON(rawData);
                
                
//         });
createallcourseschart(aggregatedData);
    }
function enrollemt_graph(Y,aggregatedData,selected_block){
    // timespent and enrollments are working for this graph
    // Verify it mrudula how to display the graphs
    $.each( aggregatedData, function( key, value ) {
        value.timespent = Math.floor(parseInt(value.timespent) / 60000);
        if(selected_block=='timespent'){
            value.customval = value.timespent;
        }
        if(selected_block=='enrollments'){
            value.customval = value.enrollments;
        }
        
    });
    
    $("#enroll_graph").empty();
        var dataArray = Object.keys(aggregatedData).map(function (key) {
            return aggregatedData[key];
        });
        // var allMonths = [
        //     "Jan", "Feb", "Mar", "Apr", "May 2023", "Jun 2023",
        //     "Jul 2023", "Aug 2023", "Sep 2023", "Oct 2023", "Nov 2023", "Dec 2023"
        // ];
        var allMonths = [
            "Jan", "Feb", "Mar", "Apr", "May", "Jun",
            "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
        ];
        var svgWidth = 900;
        var svgHeight = 400;

        // Set up margins to fit labels
        var margin = { top: 20, right: 20, bottom: 50, left: 40 };

        // Calculate the chart dimensions after considering margins
        var chartWidth = svgWidth - margin.left - margin.right;
        var chartHeight = svgHeight - margin.top - margin.bottom;
    // Create an SVG element and append it to the body of the HTML document
    var enrol_svg = d3.select("#enroll_graph")
    .append("svg")
    .attr("width", svgWidth)
    .attr("height", svgHeight);

    // Create a group element within the SVG and translate it to fit the margins
    var enrol_chartGroup = enrol_svg.append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    // Create scales for x and y axes
    var enrol_xScale = d3.scaleBand()
    .domain(allMonths) // Use allMonths array for x-axis domain
    .rangeRound([0, chartWidth])
    // .style("font-size", "14px")
    .padding(0.65);

    const enrol_yScale = d3.scaleLinear()
    .range([chartHeight, 0])
    .domain([0, d3.max(dataArray, d => d.customval)]);


    var enrol_tooltip = d3.select("#enroll_graph").append("div")
    .attr("class", "tooltip")
    .style("opacity", 0);

    enrol_chartGroup.selectAll(".bar.enrollments")
    .data(dataArray)
    .enter()
    .append("rect")
    .attr("class", "bar enrollments")
    .attr("fill","#4050E7")
    .attr("x", function (d, key) { return enrol_xScale(Object.keys(aggregatedData)[key].replace(' '+$(".course_statistics").val(),'')) + enrol_xScale.bandwidth() / 3; }) // Shift x position for grouped bars
    .attr("y", function (d) { return enrol_yScale(d.customval); })
    .attr("width", 5) // Adjust width for grouped bars
    .attr("height", function (d) { return chartHeight - enrol_yScale(d.customval); })
    .attr("rx", 3) // Set the x-axis corner radius
    .attr("ry", 3) // Set the y-axis corner radius
    .on("mouseover", function (event, d) {
        var [x, y] = d3.mouse(this);
        enrol_tooltip.transition()
            .duration(200)
            .style("opacity", 0.9);
        
        enrol_tooltip.html("enrollments: " + event.customval)
            .style("left", (x + 75) + "px")
            .style("top", (y + 100) + "px");
    })
    .on("mousemove", function (event, d) {
        var [x, y] = d3.mouse(this);
        enrol_tooltip.style("left", (x + 75) + "px")
        .style("top", (y + 100) + "px");
    })
    .on("mouseout", function () {
        enrol_tooltip.transition()
            .duration(500)
            .style("opacity", 0);
    });

    // Create x and y axes
    var enrol_xAxis = d3.axisBottom(enrol_xScale);
    var enrol_yAxis = d3.axisLeft(enrol_yScale);

    // Append x and y axes to the chart
    enrol_chartGroup.append("g")
    .attr("class", "x-axis")
    .attr("transform", "translate(0," + chartHeight + ")")
    .style("stroke-opacity", "0")
    .call(enrol_xAxis);

    enrol_chartGroup.append("g")
    .attr("class", "y-axis")
    .style("stroke-opacity", "0")
    .call(enrol_yAxis);
}
function course_statistics(Y,aggregatedData){ 
        $("#course_graph").empty();
        var dataArray = Object.keys(aggregatedData).map(function (key) {
            return aggregatedData[key];
        });

        var allMonths = [
            "Jan", "Feb", "Mar", "Apr", "May", "Jun",
            "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
        ];
        var svgWidth = 900;
        var svgHeight = 400;

        // Set up margins to fit labels
        var margin = { top: 20, right: 20, bottom: 50, left: 40 };

        // Calculate the chart dimensions after considering margins
        var chartWidth = svgWidth - margin.left - margin.right;
        var chartHeight = svgHeight - margin.top - margin.bottom;

        // Create an SVG element and append it to the body of the HTML document
        var svg = d3.select("#course_graph")
            .append("svg")
            .attr("width", svgWidth)
            .attr("height", svgHeight);

        // Create a group element within the SVG and translate it to fit the margins
        var chartGroup = svg.append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

        // Create scales for x and y axes
        var xScale = d3.scaleBand()
        .domain(allMonths) // Use allMonths array for x-axis domain
        .rangeRound([0, chartWidth])
        // .style("font-size", "14px")
        .padding(0.65);


        var yScale = d3.scaleLinear()
        .domain([0, d3.max(dataArray, function (d) {
            return Math.max(d.completions, d.enrollments);
        })])
        .nice()
        .range([chartHeight, 0]);

        
        var tooltip = d3.select("#course_graph").append("div")
        .attr("class", "tooltip")
        .style("opacity", 0);

        // Create and append the bars to the chart
        chartGroup.selectAll(".bar.completions")
            .data(dataArray)
            .enter()
            .append("rect")
            .attr("class", "bar completions")
            .attr("fill","#FBC02D")
            .attr("x", function (d, key) { return xScale(Object.keys(aggregatedData)[key].replace(' '+$(".completion_statistics").val(),'')); })
            .attr("y", function (d) { return yScale(d.completions); })
            .attr("width", 5) // Adjust width for grouped bars
            .attr("height", function (d) { return chartHeight - yScale(d.completions); })
            .attr("rx", 3) // Set the x-axis corner radius
            .attr("ry", 3) // Set the y-axis corner radius
            .on("mouseover", function (event, d) {
          
                var [x, y] = d3.mouse(this);
                tooltip.transition()
                    .duration(200)
                    .style("opacity", 0.9);
                  
                tooltip.html("enrollments: " + event.enrollments + "<br>completions: " + event.completions)
                    .style("left", (x + 75) + "px")
                    .style("top", (y + 100) + "px");
            })
            .on("mousemove", function (event, d) {
                var [x, y] = d3.mouse(this);
                tooltip.style("left", (x + 75) + "px")
                .style("top", (y + 100) + "px");
            })
            .on("mouseout", function () {
                tooltip.transition()
                    .duration(500)
                    .style("opacity", 0);
            });

        chartGroup.selectAll(".bar.enrollments")
            .data(dataArray)
            .enter()
            .append("rect")
            .attr("class", "bar enrollments")
            .attr("fill","#4050E7")
            .attr("x", function (d, key) { return xScale(Object.keys(aggregatedData)[key].replace(' '+$(".completion_statistics").val(),'')) + xScale.bandwidth() / 3; }) // Shift x position for grouped bars
            .attr("y", function (d) { return yScale(d.enrollments); })
            .attr("width", 5) // Adjust width for grouped bars
            .attr("height", function (d) { return chartHeight - yScale(d.enrollments); })
            .attr("rx", 3) // Set the x-axis corner radius
            .attr("ry", 3) // Set the y-axis corner radius
            .on("mouseover", function (event, d) {
         
                var [x, y] = d3.mouse(this);
                tooltip.transition()
                    .duration(200)
                    .style("opacity", 0.9);
                  
                tooltip.html("enrollments: " + event.enrollments + "<br>completions: " + event.completions)
                    .style("left", (x + 75) + "px")
                    .style("top", (y + 100) + "px");
            })
            .on("mousemove", function (event, d) {
                var [x, y] = d3.mouse(this);
                tooltip.style("left", (x + 75) + "px")
                .style("top", (y + 100) + "px");
            })
            .on("mouseout", function () {
                tooltip.transition()
                    .duration(500)
                    .style("opacity", 0);
            });

        // Create x and y axes
        var xAxis = d3.axisBottom(xScale);
        var yAxis = d3.axisLeft(yScale);
        
        // Append x and y axes to the chart
        chartGroup.append("g")
            .attr("class", "x-axis")
            .attr("transform", "translate(0," + chartHeight + ")")
            .style("stroke-opacity", "0")
            .call(xAxis);

        chartGroup.append("g")
            .attr("class", "y-axis")
            .style("stroke-opacity", "0")
            .call(yAxis);          
}
function process_data(returnArray){
    var participantCount = returnArray['participant_count'];
    
    var totalSeats = returnArray['total_seats'];
    var totalCourses = returnArray['total_courses'];
    var courseBasedData = returnArray['course_based_data'];
    var courses_table = returnArray['courses_table'];
    var resultArray = JSON.parse(courseBasedData);

    var dataPoints = [];
    var allcourses = [];

    $.each(resultArray, function(index, courseDataGroup) {
        var courseCounts = {};

        $.each(courseDataGroup, function(index, courseData) {
            allcourses.push(courseData);
            var courseName = courseData['name'];
            var timespent = parseInt(courseData['timespent']);
            var scormRemoteId = courseData['scorm_remote_id'];
            var completedPages = courseData['completed_pages'];
            var totalPages = courseData['total_pages'];
            
            if (!courseCounts[scormRemoteId]) {
                courseCounts[scormRemoteId] = {
                    'label': courseName,
                    'y': 1,
                    'completions': 0,
                    'timespent': timespent,
                    'scorm_remote_id': scormRemoteId,
                    'timecreated':courseData['timecreated']
                };
            } else {
                courseCounts[scormRemoteId]['y']++;
                courseCounts[scormRemoteId]['timespent'] += parseInt(timespent);
            }

            if (totalPages === completedPages) {
                courseCounts[scormRemoteId]['completions']++;
            }
        });

        dataPoints = dataPoints.concat(Object.values(courseCounts));
    });

    var rawDataJSON = JSON.stringify(resultArray);
    var tableData = JSON.stringify(dataPoints);
    // courses_table = JSON.stringify(courses_table);

    var aggregatedTImeSpent = [];
    var aggregatedEnrolments = [];
    var coursetemp = [];
    var aggregatedData = {};
    $.each(allcourses, function(index, entry) {
        var timestamp = entry["timecreated"];
        var date = new Date(timestamp * 1000);
        
        
    
        var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        var monthYear = monthNames[date.getMonth()] + " " + date.getFullYear();
       

        if (!aggregatedData[monthYear]) {
            aggregatedData[monthYear] = {
                "timespent": 0,
                "timestamp": null,
                "enrollments": 0,
                "completions": 0
            };
            
        }

        if (aggregatedData[monthYear]["timestamp"] === null) {
            aggregatedData[monthYear]["timestamp"] = timestamp;
        }

        aggregatedData[monthYear]["timespent"] += parseInt(entry["timespent"]);

        // var prevUserId = aggregatedData[monthYear]["user_id"];
        // if (prevUserId !== entry["user_id"]) {
            
        // }
        aggregatedData[monthYear]["enrollments"]++;
        aggregatedData[monthYear]["user_id"] = entry["user_id"];

        if (entry["total_pages"] === entry["completed_pages"]) {
            aggregatedData[monthYear]["completions"]++;
        }
    });
    return [tableData,rawDataJSON,allcourses,aggregatedData,courses_table];
}
function get_analytics_data(domainvalue, callback) {
    var windowurl = window.location.href;
    let siteurl = windowurl.split("/local");
    siteurl = siteurl[0].concat('/local/levitate/get_token.php');
    // siteurl = 'https://'+siteurl;
    $.ajax({url: siteurl,
        success: function(response){
            response = JSON.parse(response);
            //Handle the response here
            let processed_data = process_data(response);

            // Call the callback function with the processed data
            if (typeof callback === 'function') {
                callback(processed_data);
            }
          }
      });
}
function year_aggregated_data(aggregatedData,yearvalue){
    let filteredAggregatedData = {};
    var selectedYear = parseInt(yearvalue);
    $.each(aggregatedData, function(year, arrayvalue) {
        var dataYear = new Date(arrayvalue.timestamp * 1000).getFullYear();

        if (selectedYear == dataYear) {
            // If the selected year is different from data year, remove it from aggregatedData
            filteredAggregatedData[year] = Object.assign({}, aggregatedData[year]);
        }
    });
    return filteredAggregatedData;
}
function year_table_data(resultArray,year){
    resultArray=JSON.parse(resultArray);

    var dataPoints = [];
    $.each(resultArray, function(index, courseDataGroup) {
   
        var courseCounts = {};

        $.each(courseDataGroup, function(index, courseData) {
            var dataYear = new Date(courseData['timecreated'] * 1000).getFullYear();
            if (parseInt(year) == dataYear) {
                
                var courseName = courseData['name'];
                var timespent = parseInt(courseData['timespent']);
                var scormRemoteId = courseData['scorm_remote_id'];
                var completedPages = courseData['completed_pages'];
                var totalPages = courseData['total_pages'];
                
                if (!courseCounts[scormRemoteId]) {
                    courseCounts[scormRemoteId] = {
                        'label': courseName,
                        'y': 1,
                        'completions': 0,
                        'timespent': timespent,
                        'scorm_remote_id': scormRemoteId,
                        'timecreated':courseData['timecreated']
                    };
                } else {
                    courseCounts[scormRemoteId]['y']++;
                    courseCounts[scormRemoteId]['timespent'] += parseInt(timespent);
                }

                if (totalPages === completedPages) {
                    courseCounts[scormRemoteId]['completions']++;
                }
            }
        });

        dataPoints = dataPoints.concat(Object.values(courseCounts));
    });
    var tableData = JSON.stringify(dataPoints);
    return tableData
}
function year_rawdata_json(resultArray,year){

    $.each(resultArray, function(index, courseDataGroup) {
   
        var courseCounts = {};

        $.each(courseDataGroup, function(index, courseData) {
            var dataYear = new Date(courseData['timecreated'] * 1000).getFullYear();
            if (parseInt(year) !== dataYear) {
                
                delete courseDataGroup[index];
            }
        });

    });
    return resultArray;
}

$(document).ready(function(){
  
    let tableData,rawDataJSON,allcourses,aggregatedData,courses_table;

        get_analytics_data('', function(analytics_data) {
          

            not_processed_tableData = analytics_data[0];
            rawDataJSON = analytics_data[1];
            allcourses = analytics_data[2];
            aggregatedData = analytics_data[3];
            courses_table = analytics_data[4];
            var years = [];

            $.each(aggregatedData, function(index, arrayvalue) {
                var year = new Date(arrayvalue.timestamp * 1000).getFullYear();
                
                if ($.inArray(year, years) === -1) {
                    years.push(year);
                }
            });

            years.sort(function(a, b) {
                return a - b;
            });
            var completion_statistics_select = $(".completion_statistics");
            var course_statistics_select =$(".course_statistics");
            var table_select = $(".table_select");
            $.each( years, function( key, value ) {
                completion_statistics_select.append($('<option>', {
                    value: value,
                    text: value
                }));
                course_statistics_select.append($('<option>', {
                    value: value,
                    text: value
                }));
                table_select.append($('<option>', {
                    value: value,
                    text: value
                }));
            });

            
            enrollemt_graph('',year_aggregated_data(aggregatedData,$(".course_statistics").val()),'enrollments');
            course_statistics('',year_aggregated_data(aggregatedData,$(".completion_statistics").val()));
            let tabledata = year_table_data(rawDataJSON,$(".course_statistics").val());
            createDataTable('',tabledata,rawDataJSON,courses_table);
            
        
        });
    // }
    
    $(".completion_statistics").change(function(){
        let filteredAggregatedData = year_aggregated_data(aggregatedData,this.value);
        course_statistics('',filteredAggregatedData);
      });
      $(".course_statistics").change(function(){
        let filteredAggregatedData = year_aggregated_data(aggregatedData,this.value);
        enrollemt_graph('',filteredAggregatedData,'enrollments');
      });
      $(".table_select").change(function(){
        let filteredtabledata = year_table_data(rawDataJSON,$(".table_select").val());
        updateDataTable(filteredtabledata,rawDataJSON,courses_table);
        // createDataTable('',filteredtabledata,rawDataJSON);
      });
      $(".total-users").on('click', function() {
        $(".total-minutes").removeClass('active');
        $(".total-users").addClass("active");
        enrollemt_graph('',year_aggregated_data(aggregatedData,$(".course_statistics").val()),'enrollments');
      });
      $(".total-minutes").on('click', function() {
        $(".total-users").removeClass('active');
        $(".total-minutes").addClass("active");
        enrollemt_graph('',year_aggregated_data(aggregatedData,$(".course_statistics").val()),'timespent');
      });

  });
