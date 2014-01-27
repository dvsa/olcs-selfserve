/* 
 * Javascript for the licence page
 */

var selfserve = {}; // Initialize the selfserve object

selfserve.setSections = function ($context) {
    // Section visibility
    if ( $('input:radio[name=operatorLocation]:checked').val() != "" ) {
        $('#operatorTypeSection').show();
    } else {
        $('#operatorTypeSection').hide();
    }

    // Country logic
    if ( $('input:radio[name=operatorLocation]:checked').val() == "uk" ) {
    }
};

$(document).ready(function(){

    $('BODY').on("click","input:radio[name=operatorLocation]", function(e){
        selfserve.setSections();
    });
});