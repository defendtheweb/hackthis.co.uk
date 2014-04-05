    if (graphData) {
        // GRAPH
        var data = graphData.slice();
        var format = d3.time.format("%Y-%m-%d");
        var getCount = function(d) { return d.count };
        var getDate = function(d) { return format.parse(d.date) };

        var width = $(".graph").width() - 100;

        var margin = {top: 30, right: 20, bottom: 20, left: 30},
            width = width - margin.left - margin.right,
            height = 220 - margin.top - margin.bottom;

        var formatNumber = d3.format(".1f");
        var  date_format = d3.time.format("%a");

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
            .text(graphTitle)
            .classed("title", true);


        var line = d3.svg.line()
            .interpolate("linear")
            .x(function(n) { return xscale(getDate(n)) })
            .y(function(n) { return yscale(getCount(n)) });
        svg.append("path")
              .attr("d", line(data))
              .attr("stroke-width", "1")
              .attr("fill", "none");

    }

if (graph2Data) {
        // GRAPH 2
        var data = graph2Data.slice();
        var width = $(".graph2").width() - 100;

        var margin = {top: 30, right: 20, bottom: 20, left: 30},
            width = width - margin.left - margin.right,
            height = 220 - margin.top - margin.bottom;

        var y = d3.scale.linear()
            .domain([0, d3.max(data, function(d) { return d.count; })])
            .range([height, 0]);

        var x = d3.scale.ordinal()
            .domain(data.map(function(d) { return d.hour; }))
            .rangeRoundBands([0, width], 1);

        var xAxis = d3.svg.axis()
            .scale(x)
            .orient("bottom");

        var yAxis = d3.svg.axis()
            .scale(y)
            .ticks(4)
            .tickSize(width)
            .orient("right");

        var svg = d3.select(".graph2").append("svg")
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
            .text(graph2Title)
            .classed("title", true);

        var barWidth = width/24 - width/120;

  svg.selectAll(".bar")
      .data(data)
    .enter().append("rect")
      .attr("class", function(d) { return d.class })
      .attr("width", barWidth)
      .attr("x", function(d) { return x(d.hour) - barWidth/2; })
      .attr("y", function(d) { return y(d.count); })
      .attr("height", function(d) { return height - y(d.count); });
        // var line = d3.svg.line()
        //     .interpolate("linear")
        //     .x(function(n) { return xscale(getDate(n)) })
        //     .y(function(n) { return yscale(getCount(n)) });
        // svg.append("path")
        //       .attr("d", line(data))
        //       .attr("stroke-width", "1")
        //       .attr("fill", "none");

    }