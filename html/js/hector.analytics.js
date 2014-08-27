/**
 * @author Ubani Balogun <ubani@sas.upenn.edu>
 * @package HECTOR
 * 
 * Functions used for analytics graphics in hector
 */

/**
 * Draw a Doughnut chart that shows the top data value in the center as a percent of the remaining data
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
