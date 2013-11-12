$(function() {
    // Flag controls
    $('.flags a.remove').on('click', function(e) {
        e.preventDefault();
        var $this = $(this),
            $row = $(this).closest('tr');
        
        $.getJSON('forum.php?action=flag.remove&id='+$row.attr('data-pid'), function(data) {
            if (data.status === true) {
                $row.slideUp();
            }
        });
    });










    // GRAPH
    var data = graphData.slice();
    var format = d3.time.format("%d-%m-%Y");
    var getCount = function(d) { return d.count };
    var getDate = function(d) { return format.parse(d.date) };

    var width = $(".graph").width();

    var margin = {top: 30, right: 20, bottom: 20, left: 30},
        width = width - margin.left - margin.right,
        height = 180 - margin.top - margin.bottom;

    var formatNumber = d3.format(".1f");
    var  date_format = d3.time.format("%d %b");

    var xscale = d3.time.scale().domain(d3.extent(data, getDate)).range([0, width]);
    var yscale = d3.scale.linear().domain(d3.extent(data, getCount)).range([height,0]);

    var y = d3.scale.linear()
        .domain(d3.extent(data, getCount)).nice()
        .range([height, 0]);

    var x = d3.time.scale()
        .domain(d3.extent(data, getDate))
        .range([0, width]);

    var xAxis = d3.svg.axis()
        .scale(x)
        .ticks(d3.time.hours, 24)
        .tickFormat(date_format)
        .orient("bottom");

    var yAxis = d3.svg.axis()
        .scale(y)
        .ticks(4)
        .tickSize(width)
        .orient("right");

    var svg = d3.select(".graph").append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
      .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    svg.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis);

    var gy = svg.append("g")
        .attr("class", "y axis")
        .attr("transform", "translate(0,0)")
        .call(yAxis);

    gy.selectAll("g").filter(function(d) { return d; })
        .classed("minor", true);

    gy.selectAll("text")
        .attr("x", -30)
        .attr("dy", 4);

    svg.append("text")
        .attr("text-anchor", "middle")
        .attr("transform", "translate("+ (width/2) +",-18)")
        .text("New forum posts over the last 7 days")
        .classed("title", true);


    var line = d3.svg.line()
        .interpolate("linear")
        .x(function(n) { return xscale(getDate(n)) })
        .y(function(n) { return yscale(getCount(n)) });
    svg.append("path")
          .attr("d", line(data))
          .attr("stroke-width", "1")
          .attr("fill", "none");
});