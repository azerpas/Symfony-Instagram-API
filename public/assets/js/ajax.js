
// send config forms to server
function searchBot_config() {
    //get parameters value 
    
    
    
    $.ajax({
        type:'post',
        data:$("[name=searchForm]").serialize() ,
        url:'/instagui/set_search_bot',
       
        success:function(data){
            console.log(data.output);
             $.notify(
                {   icon:'fa fa-check-circle', 
                    title: "<strong>Success :</strong> ", 
                    message:'Configuration sauvgarder'}
                 ,
                {type:'success',
                 delay: 5000,
                   timer: 1000,
                 offset: 50} 
               );
        },
        error: function(response) {
                      
                       //on affiche les erreurs...
                        $.notify(
                          {
                             icon:'fa fa-exclamation-circle', 
                             title: "<strong>"+textStatus+" :</strong> ", 
                             message:jqXHR.status+" "+errorThrown
                            }   
                           ,
                         {type:'danger',
                           delay: 5000,
                           timer: 1000,
                           offset: 50} 
                         );
                       },
                 
    })
     
   }


// turn on/off a bot   
   function run_bot(toggle) {
    var bot= toggle.id;//get bot name
    
   if($(toggle).prop("checked") == true){
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
{   icon:'fa fa-check-circle', 
title: "<strong>Success :</strong> ", 
message:data.output}
,
{type:'success',
delay: 5000,
timer: 1000,
offset: 50} 
);
},
error: function(jqXHR, textStatus, errorThrown) {
  if($(toggle).prop("checked") == true){
         toggle.checked=false;
        
        }else{
        
         toggle.checked=true;
        }
  $.notify(
   {   icon:'fa fa-exclamation-circle', 
       title: "<strong>"+textStatus+" :</strong> ", 
       message:jqXHR.status+" "+errorThrown
       }
     ,
   {type:'danger',
     delay: 5000,
     timer: 1000,
     offset: 50} 
   );
 },              
})     
  }