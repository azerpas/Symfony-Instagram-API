  //custom js file
 
  $(document).ready(function(){
     //date picker
    var date_input=$('input[name="dateDebut"]'); //our date input has the name "date"
    var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
    var options={
      format: 'yyyy/mm/dd',
      container: container,
      todayHighlight: true,
      autoclose: true,
    };
    date_input.datepicker(options);
    // modal 
       $('#myModal').on('click', function () {
      console.log("test");
      var tmpid = $(document.activeElement).attr('id'); 
 
     $('#myInput').trigger('focus');
      })
  
  
  
   });


// send config forms to server
function config() {
    //get parameters value 
    
    
    
    $.ajax({
        type:'post',
        data:$("[name=configForm]").serialize() ,
        url:'/instagui/config_bot',
       
        success: function(data){
            console.log(data.output);
             $.notify(
                 {
                     icon:'fa fa-check-circle',
                     title: "<strong>Success :</strong> ",
                     message:'Configuration sauvgarder'
                 },
                 {
                     type:'success',
                     delay: 5000,
                     timer: 1000,
                     offset: 50
                 });
        },
        error: function(response) {
                      
                       //on affiche les erreurs...
            $.notify(
                {
                    icon:'fa fa-exclamation-circle',
                    title: "<strong>"+textStatus+" :</strong> ",
                    message:jqXHR.status+" "+errorThrown
                },
                {
                    type:'danger',
                    delay: 5000,
                    timer: 1000,
                    offset: 50
                }
             );
        },
    });
}

// turn on/off a bot   
function run_bot(toggle) {
    var bot= toggle.id;//get bot name

    if($(toggle).prop("checked") === true){
        var value="on";//set value on
    }else{
        var value="off";//set value off
    }
    //ajax server request
    $.ajax({
        type:'post',
        data:{"bot":bot,"value":value} ,
        url:'/instagui/set_bot_status',

        success:function(data){
            $.notify(
                {
                    icon:'fa fa-check-circle',
                    title: "<strong>Success :</strong> ",
                    message:data.output
                },
                {
                    type:'success',
                    delay: 5000,
                    timer: 1000,
                    offset: 50
                });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            if($(toggle).prop("checked") === true){
                toggle.checked=false;
            }else{
                toggle.checked=true;
            }
            $.notify(
                {
                    icon:'fa fa-exclamation-circle',
                    title: "<strong>"+textStatus+" :</strong> ",
                    message:jqXHR.status+" "+errorThrown
                },
                {
                    type:'danger',
                    delay: 5000,
                    timer: 1000,
                    offset: 50
                });
        },
    });
}