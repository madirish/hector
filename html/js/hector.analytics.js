/**
 * @author Ubani Balogun <ubani@sas.upenn.edu>
 * @author Justin C. Klein Keane <justin@madirish.net>
 * @package HECTOR
 * 
 * Functions used for analytics graphics in hector
 */

/**
 * Draw a Chart.js Doughnut chart that shows the top data value in the center as a percent of the remaining data
 * 
 * @param canvasId the id of the canvas element used for the chart
 * @param dataId The id of the hidden div that contains the data element
 */
function hectorDrawDoughnutChart(canvasId,dataId,percentLabel){
	if (canvasId == null || dataId == null){
		return
	}
	
	if (document.getElementById(canvasId) != null & document.getElementById(dataId) != null){
		cId = "#" + canvasId;
		dId = "#" + dataId;
		font = "45px Helvetica Neue, Helvetica, Arial, sans-serif";
		fillStyle = 'black';
		textAlign = 'center';
		var percent = parseInt($(dId).text());
		var restPercent = 100-percent;

		var data = {
				labels: [percentLabel,"All others"], //These are hidden anyway
				datasets: [{
					data: [percent, restPercent],
					backgroundColor: ["#acdcee", "#999999"]
				}]
		};
	    
	    var ctx = document.getElementById(canvasId).getContext("2d");
	    ctx.canvas.width = 200;
		ctx.canvas.height = 200;
	    var chart = new Chart(ctx,{
	    	type: 'doughnut',
	    	data: data,
	    	options: {
	    		legend: {display: false}, // Hide the labels
		    	animationSteps:1,
		    	percentageInnerCutout : 80,
		    	showTooltips: false,
	            animation: {
	                duration: 100,
	                onComplete: function(){
	                    var canvas = document.getElementById(canvasId);
	                    // calculate the center of the canvas (cx,cy)
	                    var cx=canvas.width/2;
	                    var cy=canvas.height/2;
	                    // horizontally align text around the specified point (cx)
	                    ctx.textAlign='center';

	                    // vertically align text around the specified point (cy)
	                    ctx.textBaseline='middle';

	                    // draw the text
	                    ctx.font = font;
	                    ctx.fillStyle = fillStyle;
	                    ctx.textAlign = textAlign;
	                    ctx.fillText(percent + "%", cx,cy);
	                }
	            },
		    	
	    	}
	    });
        
	}
    
}

/**
 * Draw a Chart.js Bar chart with the given labels and values
 * @param canvasId the id of the canvas element used for the chart
 * @param labelsId The id of a hidden div that contains the json encoded labels for the Chart 
 * @param valuesId The id of a hidden div that contains the json encoded values for the Chart
 * @param datasetProperties An optional javascript object for modifying the chart appearance (See Chart.js Bar chart Datasets properties)
 */
function hectorDrawBarChart(canvasId,labelsId,valuesId,datasetProperties){
	if (canvasId == null || labelsId == null || valuesId == null){
		return;
	}
	
	if (document.getElementById(labelsId) != null && document.getElementById(valuesId) != null && document.getElementById(canvasId) != null){
		var lId = "#" + labelsId;
		var vId = "#" + valuesId;
		var labels = $.parseJSON($(lId).text());
		var values = $.parseJSON($(vId).text());
		var datasets;
		
		if (datasetProperties && typeof datasetProperties == 'object'){
			datasetProperties['data'] = values;
			datasets = [datasetProperties];
		}else{
			datasets = [{
				fillColor: "#05EDFF",
				strokeColor: "#05EDFF",
	        	pointColor: "#05EDFF",
	        	pointStrokeColor: "#fff",
	        	pointHighlightFill: "#fff",
	        	pointHighlightStroke: "rgba(220,220,220,1)",
	        	data: values,
			}]
		}
			
		data = {
				labels: labels,
				datasets: datasets, 
		};
		var options = {
				multiTooltipTemplate: "<%= datasetLabel%> - <%= value %>",
		};
		
		var chart = new Chart(document.getElementById(canvasId).getContext("2d"), {
			type: 'bar',
			data,
			options
		});
		
	}
	
}