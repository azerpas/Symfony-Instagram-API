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

function searchSettings() {
    $.ajax({
       type:'post'
    });
}

// set bot config
function config() {
   
    $.ajax({
        type:'post',
        data:$("[name=configForm]").serialize() ,
        url:'/ajax/config_bot',
       
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
   

    if($(toggle).prop("checked") === true){
        var status=true;//set value on
    }else{
        var status=false;//set value off
    }
    //ajax server request
    $.ajax({
        type:'post',
        data:{"status":status} ,
        url:'/ajax/set_bot_status',

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



runTestIgAcc = () => {
    let username = $('#form_username').val();
    let password = $('#form_password').val();
    if(username === "" || password === ""){
        $.notify(
            {
                icon:'fa fa-exclamation-circle',
                title: "<strong>Form data</strong> ",
                message:"Username or password empty"
            },
            {
                type:'danger',
                delay: 3000,
                timer: 1000,
                offset: 50
            }
        );
        return;
    }
    $.ajax({
        'type':'post',
        'data':{
            'username':username,
            'password':password
        },
        'url':'/instagui/testIgAccount',
        complete:function(data,status){
            if(data.status !== 200){
                return;
            }
            //console.log('COMPLETE:')
            //console.log(data.responseJSON.output);
            //console.log(data.status);
            $.notify({
                icon:'fa fa-check-circle',
                title: "<strong>Success :</strong> ",
                message:data.responseJSON.output // data below
            },
            {
                type:'success',
                delay: 5000,
                timer: 1000,
                offset: 50
            });
        },
        error:function(jqXHR, textStatus) {
            //console.log('ERROR');
            //console.log(jqXHR.responseJSON);
            //console.log(jqXHR.responseJSON.status);
            $.notify({
                    icon:'fa fa-exclamation-circle',
                    title: "<strong>"+textStatus+" :</strong> ",
                    message:jqXHR.responseJSON.output
            },
            {
                type:'danger',
                delay: 5000,
                timer: 1000,
                offset: 50
            });
        }

    });
    return true;
}


//set slot status on
function activer(tmpid) {
    //ajax server request
    $.ajax({
       type:'post',
       data:{"slot":tmpid,"value":'on'} ,
       url:'/ajax/set_slot',

       success:function(data){
           document.getElementById(tmpid).className = "btn btn-primary";
           $.notify(
               {
                   icon:'fa fa-check-circle',
                   title: "<strong>Success :</strong> ",
                   message:"slot activate"
               },
               {
                   type:'success',
                   delay: 5000,
                   timer: 1000,
                   offset: 50
               });
       },
       error: function(jqXHR, textStatus, errorThrown) {
          
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
    //hide modal window
   $('#exampleModal').modal('hide');
  
}
//set slot status off 
function desactiver(tmpid) {
    //ajax server request
    $.ajax({
       type:'post',
       data:{"slot":tmpid,"value":'off'} ,
       url:'/ajax/set_slot',

       success:function(data){
           document.getElementById(tmpid).className = "btn btn-basic";
           $.notify(
               {
                   icon:'fa fa-check-circle',
                   title: "<strong>Success :</strong> ",
                   message:"slot deactivated"
               },
               {
                   type:'success',
                   delay: 5000,
                   timer: 1000,
                   offset: 50
               });
       },
       error: function(jqXHR, textStatus, errorThrown) {
          
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
//hide modal window
$('#exampleModal').modal('hide');
}

editProfile = () => {
    $.ajax({
        type:'post',
        data:$("[name=profile]").serialize() ,
        url:'/ajax/edit_profile',
       
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