/**
 * @author Ubani Balogun <ubani@sas.upenn.edu>
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
function hectorDrawDoughnutChart(canvasId,dataId){
	if (canvasId == null || dataId == null){
		return
	}
	
	if (document.getElementById(canvasId) != null & document.getElementById(dataId) != null){
		cId = "#" + canvasId;
		dId = "#" + dataId;
		font = "50px Helvetica Neue, Helvetica, Arial, sans-serif";
		fillStyle = 'black';
		textAlign = 'center';
		var percent = parseInt($(dId).text());
	    var data = [
	        {
	          value: percent,
	          color:"#05EDFF"
	        },
	        {
	          value : 100-percent,
	          color : "#fafafa"
	        }
	      ];
	    
	    
	    var ctx = document.getElementById(canvasId).getContext("2d");
	    ctx.canvas.width = 200;
		ctx.canvas.height = 200;
	    var chart = new Chart(ctx).Doughnut(data,{
	    	animationSteps:1,
	    	percentageInnerCutout : 80,
	    	showTooltips: false,
	    	onAnimationComplete: function(){
	    		ctx.font = font;
	    		ctx.fillStyle = fillStyle;
	    		ctx.textAlign = textAlign;
	    		ctx.fillText(data[0].value + "%", ctx.canvas.width/2, ctx.canvas.width/2 + 15);
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
		
		var chart = new Chart(document.getElementById(canvasId).getContext("2d")).Bar(data,options);
		
	}
	
}