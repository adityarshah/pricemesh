(function ($){
    "use strict";

    $(function(){

        var Pricemesh = {

            remove_pids: function(string){
                /** takes a string in the format of: pid0,pid1,pid2 and removes all pids
                 * from the hidden field and the tags from the DOM */
                string.split(",").forEach(function(pid){
                    Pricemesh.remove_pid(pid);
                });
            },

            add_pids: function(string){
                /** takes a string in the form of: pid0,pid1,pid and adds the pids
                 * to the hidden field and creates new tags and adds them to the DOM */

                string.split(",").forEach(function(pid){
                    pid = Pricemesh.create_pid(pid);
                    if(pid){
                        Pricemesh.add_pid(pid);
                    }
                });
            },

            remove_pid: function(pid){
                /** removes the PID from the DOM. This includes the tag and the hidden pricemesh_pids field */

                Pricemesh.remove_pid_from_hidden_field(pid);
                Pricemesh.remove_pid_tag(pid);

            },

            add_pid: function(pid){
                /** takes a pid, adds it to the hidden field and creates a tag that is added to the DOM */
                Pricemesh.add_pid_to_hidden_field(pid);
                Pricemesh.add_pid_tag(pid);
            },

            add_pid_to_hidden_field: function(pid){
                var hidden_field = $("#pricemesh_pids");
                hidden_field.val(hidden_field.val() + "," + pid);
                Pricemesh.sanitize_hidden_field();
            },

            remove_pid_from_hidden_field: function(pid){
                var hidden_field = $("#pricemesh_pids");
                var new_value = "";
                hidden_field.val().split(",").forEach(function(current_pid){

                    if(current_pid != pid){
                        new_value = new_value + "," + current_pid;
                    }
                });

                hidden_field.val(new_value);

                Pricemesh.sanitize_hidden_field();
            },

            sanitize_hidden_field: function(){
                /** makes sure that no leading or trailing commas are in the hidden field **/
                var hidden_field = $("#pricemesh_pids");

                if(hidden_field.val().slice(-1) == ',') {
                    hidden_field.val(hidden_field.val().slice(0, -1));
                }

                if(hidden_field.val().slice(0,1) == ',') {
                    hidden_field.val(hidden_field.val().slice(1));
                }
            },

            add_pid_tag: function(pid){
                //create a span tag and add it to the DOM
                var span = document.createElement("span");
                span.innerHTML = '<a class="pricemesh_remove" id="pricemesh_pid_'+ pid +'" class="ntdelbutton">X</a>&nbsp;'+ pid;
                $("#pricemesh_pids_field").append(span);
            },

            remove_pid_tag: function(pid){
                $("#pricemesh_pid_" + pid).parent().remove();
            },

            create_pid: function(string){
                /** tries to create a PID from `string`. Either returns a string representing a valid PID, or false **/

                    //sanitize the input
                string = Pricemesh.sanitize_pid(string);

                //check that the pid has not already been added
                if(Pricemesh.has_pid(string)){
                    return false;
                }

                //if the sanitized PID is not valid, return false
                if(!Pricemesh.is_valid_pid(string)){
                    return false;
                }

                //if max PID limit is exceeded, return false
                if(!Pricemesh.can_create_more_pids(string)){
                    return false;
                }

                //all formatting and tests passed, return the string
                return string;
            },

            can_create_more_pids: function(string){
                /** MAX for pids is 20 per request. If this limit is exceeded return false. true otherwise **/
                if($(".pricemesh_remove").length >= 20){
                    alert("Pro Anfrage können maximal 20 Barcodes hinzugefügt werden.");
                    return false;
                }

                return true;
            },

            is_valid_pid: function(string){
                /** takes a string and validates it as a PID */

                //If the string is shorter than 10 or longer than 14 chars it can't be a ASIN, EAN or UPC
                if(string.length < 10 || string.length > 14){
                    return false;
                }

                return true;
            },

            has_pid: function(string){
                /** returns `true` if the PID has already been added */
                var value = $("#pricemesh_pids").val();
                var has_pid = false;
                value.split(",").forEach(function(pid){
                    if(string == pid){
                        has_pid = true;
                    }
                })

                return has_pid;

            },

            sanitize_pid: function(str){
                /** takes a string containing a PID or a link from amazon and returns a candidate for a PID as a string */
                str = str.replace(/^\s+|\s+$/g, '');

                //remove amazon shit
                var asin_regex = RegExp("^(http[s]?://)?([\\w.-]+)(:[0-9]+)?/([\\w-%]+/)?(dp|gp/product|exec/o‌​bidos/asin)/(\\w+/)?(\\w{10})(.*)?$");
                var match = str.match(asin_regex);
                if(match){
                    return match[7];
                }
                str = str.replace("-", "");
                return str;
            },

            search: function(){
                $("#pm-search-loader").removeClass("hidden");
                $("#pm-results").empty();
                $("#pm-search-error").addClass("hidden")
                $.ajax({
                    url: 'https://www.pricemesh.io/api/v1/search/',
                    type: 'post',
                    headers: {
                        "Authorization": "Token " + $("#pm-id_api_token").val()
                    },
                    dataType: 'json',
                    data: {
                        "query": $("#pm-id_query").val(),
                        "country": $("#pm-id_country").val(),
                        "not_words": $("#pm-id_not_words").val(),
                        "price_min": $("#pm-id_price_min").val(),
                        "price_max": $("#pm-id_price_max").val(),
                        "deepsearch": $("#pm-id_deepsearch").is(':checked')
                    },
                    success: function(data, status, jqXHR){
                        try{
                            var resp = JSON.parse(jqXHR.responseText);
                            var templateScript = $("#pm-products").html();
                            var template = Handlebars.compile(templateScript);
                            $("#pm-results").removeClass("hidden").append(template(resp.results));
                        }catch(err){
                            $("#pm-search-error").removeClass("hidden");
                            $("#pm-search-error-message").empty().text("JSON PARSE ERROR");
                        }

                        //@todo add message on empty response

                    },
                    complete: function(){
                        $("#pm-search-loader").addClass("hidden");
                    },
                    error: function(data){
                        $("#pm-search-error").removeClass("hidden");
                        $("#pm-search-error-message").empty().text(data.status + " " + data.statusText);
                    }
                });
            },

            add_button_triggered: function(){
                var input_field = $("#pricemesh_new_pid");
                var pid = Pricemesh.create_pid(input_field.val());
                if(pid){
                    Pricemesh.add_pid(pid);
                    input_field.val("");
                }else{
                    document.getElementById("pricemesh_new_pid").style.borderColor = "red";
                    setTimeout ('document.getElementById("pricemesh_new_pid").style.borderColor = "#dfdfdf"',500);
                }
            }

        }

        $(document).ready(function(){
            $("#pm-searchbutton").click(function() {
                Pricemesh.search();
            });

            $("#pricemesh_add_new_pid_btn").click(function(){
                Pricemesh.add_button_triggered();
            });

            $("#pricemesh_new_pid").keypress(function(e){
                /** listens on the PID add field. If ENTER is pressed, add the PID that is in the field */
                if(e.which == 13){
                    Pricemesh.add_button_triggered();
                    return false;
                }
            });


            $("#pm-extended-search").click(function(){
                $("#pm-extended-search-form").toggleClass("hidden");
                $("#pm-extended-search-icon").toggleClass("pm-chevron-down pm-chevron-right");
            });


            $("#pm-copy-button").click(function(){
                $("#pm-copyhelper_field").focus().select();
            });

            $(document).on("click", ".pm-expand", function(){
                $(this).toggleClass("fa-minus-circle fa-plus-circle");
                var content_id = $(this).attr("id").replace("_plus", "");
                $("#" + content_id).toggleClass("hidden");
            });

            $(document).on("click", ".pm-add", function(){
                //$(this).parent().toggleClass("fa-minus-circle fa-plus-circle");
                var clicked = $(this).data("clicked");
                var content_id = $(this).data("id");
                if(clicked){
                    $(this).data("clicked", false);
                    $("#pm-icon" + content_id).removeClass("pm-minus").addClass("pm-plus");
                    $(this).removeClass("pm-btn-danger").addClass("pm-btn-primary");
                    $("#pm-panel" + content_id).removeClass("pm-panel-success").addClass("pm-panel-default");
                    Pricemesh.remove_pids($("#pm-pids" + content_id).data("pids"));
                }else{
                    $(this).data("clicked", true);
                    $(this).removeClass("pm-btn-primary").addClass("pm-btn-danger");
                    $("#pm-icon" + content_id).removeClass("pm-plus").addClass("pm-minus");

                    $("#pm-panel" + content_id).removeClass("pm-panel-default").addClass("pm-panel-success");
                    Pricemesh.add_pids($("#pm-pids" + content_id).data("pids"));
                }
                return false;
            });

            $(document).on("click", ".pricemesh_remove", function(){
                /** onclick function of the tags, holding the PID */
                Pricemesh.remove_pid($(this).attr("id").replace("pricemesh_pid_", ""));
            });
        });

    });

}(jQuery));

Handlebars.registerHelper('ifEqual', function(v1, v2, options) {
    if(v1 === v2) {
        return options.fn(this);
    }
    return options.inverse(this);
});

Handlebars.registerHelper('pluralize', function(number, single, plural) {
    return (number === 1) ? single : plural;
});