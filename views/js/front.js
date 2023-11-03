/**
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/
//Slider//
function itivos_slider(autoInicio, btnControl){
	//div para contenidos
	$(".itivos_slider").append("<div id='divContenidoSlider'></div>");
	var num_diapositivas = $(".itivos_slider  img").length; 
	// Botones Bottom
	centinela = 1;
	controlC  = parseInt(num_diapositivas)+1;
	if (num_diapositivas > 1) {
		$(".itivos_slider").after("<div id='btnSliderBottom' class='row'  style='text-align:center;'></div>");
		while (centinela<controlC) {
			$("#btnSliderBottom").append("<div class='controlSli' id='sli"+centinela+"'></div>");
			centinela++;
		};
	}
	//URL IMAGENES
	var images = $('.itivos_slides li').children('img').map(function(){
    return $(this).attr('src')
	}).get();
	//URL A VALORES
	$(".itivos_slider").append("<input type='hidden' id='urls' value='"+images+"'>");
	var urlsImg = $("#urls").val();
	if (urlsImg != null) {
	//Array de imagenes
	var arrayImg = urlsImg.split(',');
		//ocultar imagenes
		$(".itivos_slides img").hide();
		//ocultar contenidos
		$(".itivos_slides li").hide();

		var contenido = $(".itivos_slides").html();
		$(".itivos_slider").append("<div id='contenidoSlider'>"+contenido+"</div>");
		var contiene = $("#contenidoSlider li img").remove();
		var este 	 = $("#contenidoSlider").html();


		var items = $("#contenidoSlider").find('li').map(function() {
		var item = { };

		  item.id = this.value;
		  item.content = $(this).html();

		  return item;
		});
		var json  = JSON.stringify(items);
		var jsonObjet	 = $.parseJSON(json);
		var arrayContent = jsonObjet;

		// Sacar el alto mas alto
		function alto() {
			$.each(arrayImg, function(index, value) { 
			  var tmpImg = new Image();
				tmpImg.src=value; 
				$(tmpImg).on('load',function(){
				  var masAlta  = 0;	
				  var orgHeight = tmpImg.height;
				  if (orgHeight>masAlta){masAlta=orgHeight}
				  $(".itivos_slider").css("height",masAlta);
				}); 
			});
		}

		alto();


		function estilos(){
			$(".itivos_slider").fadeTo();
			$(".itivos_slider").css('width', '100%');
			$(".itivos_slider").css('background-size','cover');
			$(".itivos_slider").css('background-position','center');
			$(".itivos_slider").css('background-color','grey');
			$(".itivos_slider").css('position','relative');
			$("#divContenidoSlider").css("display","none");
		}

		estilos();

		function estilosFinos() {
		        $(".itivos_slider").fadeIn("400", function() {
		        	$("#divContenidoSlider").fadeIn();
		        });
		}

		function botonesSlider(inicio) {
			$(".activoSli").removeClass("activoSli");
			$("#sli"+inicio).toggleClass("activoSli");
			centinela2 = 1;
			controlImg = 0;
			while (centinela2<controlC) {
				$("#sli"+centinela2).attr("url",arrayImg[controlImg]);
				centinela2++;
				controlImg++;
			}
		}
		if (num_diapositivas>1) {
			speed = $("#itivos_slider_speed").val();
			var controlAuto 	= setInterval(fondoDefault, speed);
		}

		function fondoDefault() {
			estilos();

			var maximo  		= num_diapositivas-1;
			var urlFondo		= arrayImg['0'];
			var entero			= 1;
			var contenidoActual = arrayContent; 
			inicio      	    = $(".itivos_slider").attr("cuantos");
			if (inicio>maximo) {
				inicio=0;
			}
			if (inicio==null) {
				inicio=0;
				$(".itivos_slider").attr("cuantos",inicio);
			}
		
			else {
				urlFondo 		= arrayImg[inicio];
			}
			$("#divContenidoSlider").html(contenidoActual[inicio].content);
			inicio 		  = parseInt(inicio)+parseInt(entero);
			$(".itivos_slider").attr("cuantos",inicio);
			$(".itivos_slider").css('background-image', 'url('+urlFondo+')');
			estilosFinos();
			botonesSlider(inicio);
		}

		fondoDefault();

		$( ".controlSli" ).on( "click", function() {
			var urlAPoner = $(this).attr("url");
			var donde     = $(this).attr("id");
			var res       = donde.replace("sli","");
			var res2	  = res-1;
			var contenidoActual = arrayContent; 

			$(".itivos_slider").css('background-image', 'url('+urlAPoner+')');
			$("#divContenidoSlider").html(contenidoActual[res2].content);
			estilos();
			estilosFinos();
			botonesSlider(res);
			clearInterval(controlAuto); 
		});

		$('.itivos_slider').hover(
		  function () {
		    $('.botonDerecha').show("slow");
		    $('.botonIzquierda').show("slow");

		  }, 
		  function () {
		    $('.botonDerecha').hide("slow");
		    $('.botonIzquierda').hide("slow");
		  }
		);
	};
};

speed = $("#itivos_slider_speed").val();
$('.itivos_carousel_slick').slick({
  slidesToShow: 1,
  slidesToScroll: 1,
  dots:true,
  autoplay: true,
  autoplaySpeed: speed,
});
$( document ).ready(function($) {
  itivos_slider();
  $('#itivos_carousel').removeClass('itivos_no_focus_slick');
});
