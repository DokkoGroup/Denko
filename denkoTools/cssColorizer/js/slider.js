window.addEvent('domready', function(){

	// Second Example
	var el = $('setColor'), color = [iniColors[0], iniColors[1] , iniColors[2]];
	
	var updateColor = function(){
		// Sets the color of the output text and its text to the current color
		el.setStyle('color', color).set('text', color.rgbToHex());
		$('colorInput').set('value',color.rgbToHex());
		$('colorBox').set('style','background-color:'+color.rgbToHex()+';');

		document.getElementById('red').value = color[0];
		document.getElementById('green').value = color[1];
		document.getElementById('blue').value = color[2];
	};
	
	// We call that function to initially set the color output
	updateColor();
	
	$$('div.slider.advanced').each(function(el, i){
		if(this.id == 'red'){
			alert('sip...');
		}
		var slider = new Slider(el, el.getElement('.knob'), {
			steps: 255,  // Steps from 0 to 255
			wheel: true, // Using the mousewheel is possible too
			onChange: function(){
				// Based on the Slider values set an RGB value in the color array
				color[i] = this.step;
				// and update the output to the new value
				updateColor();
			}
		}).set(iniColors[i]);
	});
});