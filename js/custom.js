var Pricemesh = {

    remove_pid: function(){
        var val = this.id.replace("pricemesh_pid_", "");
        var input = document.getElementById("pricemesh_pids");
        input.value = input.value.replace(val+",","");
        input.value = input.value.replace(val, "");

        this.parentNode.parentNode.removeChild(this.parentNode);
    },

    make_pid: function(){
        //console.log("make_pid");
        var candidate = Pricemesh.strip(document.getElementById("pricemesh_new_pid").value);
        document.getElementById("pricemesh_new_pid").value = "";

        if(candidate.length < 10 || candidate.length > 14){
            document.getElementById("pricemesh_new_pid").style.borderColor = "red";
            setTimeout ('document.getElementById("pricemesh_new_pid").style.borderColor = "#dfdfdf"',500);
        }else{
            if(document.getElementById("pricemesh_pid_"+candidate) == null){


                if(document.getElementById("pricemesh_pids").value.indexOf(candidate) == -1){
                    var span = document.createElement("span");
                    span.innerHTML = '<a class="pricemesh_remove" id="pricemesh_pid_'+ candidate +'" class="ntdelbutton">X</a>&nbsp;'+ candidate;
                    document.getElementById("pricemesh_pids_field").appendChild(span);
                    document.getElementById("pricemesh_pid_"+candidate).onclick = Pricemesh.remove_pid;

                    document.getElementById("pricemesh_pids").value += (candidate + ",");
                    //console.log("adding " + candidate + ",");
                    console.log("pricemesh_pids is now: " + document.getElementById("pricemesh_pids").value);
                }
            }
        }



    },

    strip: function(str){
        str = str.replace(/^\s+|\s+$/g, '');
        //remove amazon shit
        var asin_regex = RegExp("^(http[s]?://)?([\\w.-]+)(:[0-9]+)?/([\\w-%]+/)?(dp|gp/product|exec/o‌​bidos/asin)/(\\w+/)?(\\w{10})(.*)?$");
        var match = str.match(asin_regex);
        //console.log(match);
        if(match){
            return match[7];
        }
        str = str.replace("-", "");
        return str;
    },

    key_down: function(evt){
        var keyCode = evt ? (evt.which ? evt.which : evt.keyCode) : event.keyCode;
        if (keyCode == 13) {

            evt.preventDefault();
            return false;
        }
        else {
            return true;
        }
    },

    key_up: function(evt){
        var keyCode = evt ? (evt.which ? evt.which : evt.keyCode) : event.keyCode;
        if (keyCode == 13) {
            Pricemesh.make_pid();
            return false;
        }
        else {
            return true;
        }
    },


    load: function(){
        //console.log("load");
        document.getElementById("pricemesh_add_new_pid_btn").onclick = Pricemesh.make_pid;
        document.getElementById("pricemesh_new_pid").onkeydown = Pricemesh.key_down;
        document.getElementById("pricemesh_new_pid").onkeyup = Pricemesh.key_up;
    }

}

window.addEventListener ?
window.addEventListener("load",Pricemesh.load, false) :
window.attachEvent && window.attachEvent("onload", Pricemesh.load);


