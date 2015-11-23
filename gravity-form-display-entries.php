<?php
/*
Plugin Name: IFF Competition Entries Plugin
Description: Plugin for displaying fencers who have entered a competition
Author: Scott O'Malley
Version: 1.0
*/
//------------------------------------------


function getEntries($formID)
{
    if (class_exists("GFForms")) {
        
        $paging = array(
            'offset' => 0,
            'page_size' => 200
        );
        $sorting  = array();
        $total_count = 0;        
        $search_criteria = array();
        
        $unfilteredEntries = GFAPI::get_entries(5, $search_criteria, $sorting, $paging, $total_count);
        $filteredEntries   = array();
        foreach ($unfilteredEntries as $value) {
            array_push($filteredEntries, iff_competition_filterEntry($value));
        }
        return $filteredEntries;
    }
    return null;
}

function iff_competition_filterEntry($value)
{
    $eventsEntered = array();
    
    array_push($eventsEntered, humanReadableEntry($value['38.1']));  
    array_push($eventsEntered, humanReadableEntry($value['38.2']));  
    array_push($eventsEntered, humanReadableEntry($value['38.3']));  
    array_push($eventsEntered, humanReadableEntry($value['38.4']));  
    array_push($eventsEntered, humanReadableEntry($value['38.5']));  
    array_push($eventsEntered, humanReadableEntry($value['38.6']));  

    return array(       
        'firstName' => $value['2.3'],
        'lastName' => $value['2.6'],
        'nationality' => $value['46.6'],
        'club' => ($value['10'] != 'Other (please specify)') ? $value['10'] : $value['11'],
        'events' => $eventsEntered    
    );
}

function humanReadableEntry($value)
{
    $values = array(
        "Women's Foil" => 'WF',
       
        "Women's Sabre" => 'WS',

        "Women's Epee" => 'WE',

        "Men's Foil" => 'MF',
       
        "Men's Sabre" => 'MS',
       

        "Men's Epee" => 'ME'
       
    );
    
   return $values[$value];
    
}


function entryLookup($atts)
{
    
    $attributes = shortcode_atts(array(
        'formid' => ''
    ), $atts);
    
    if ($attributes[formid] != '') {
        $entires = getEntries($attributes['formid']);
        
        /* Turn on buffering */
        ob_start();
        ?>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular.min.js"></script>
  <script>
    angular.module('entries', []).controller('controller',  function ($scope, $filter) {
        $scope.members = <?php echo json_encode($entires); ?>;

        $scope.ME = $filter('filter')($scope.members, function(m){return m.events.indexOf('ME') != -1;});
        $scope.MF = $filter('filter')($scope.members, function(m){return m.events.indexOf('MF') != -1;});
        $scope.MS = $filter('filter')($scope.members, function(m){return m.events.indexOf('MS') != -1;});
        $scope.WE = $filter('filter')($scope.members, function(m){return m.events.indexOf('WE') != -1;});
        $scope.WF = $filter('filter')($scope.members, function(m){return m.events.indexOf('WF') != -1;});
        $scope.WS = $filter('filter')($scope.members, function(m){return m.events.indexOf('WS') != -1;});


        $scope.events = [
          {
            title : "Men's Epee",
            entrants: $scope.ME 
          },
           {
            title : "Men's Foil",
            entrants: $scope.MF 
          },
           {
            title : "Men's Sabre",
             entrants: $scope.MS 

          },
            {
            title : "Women's Epee",
             entrants: $scope.WE 
          },
           {
            title : "Women's Foil",
             entrants: $scope.WF 
          },
           {
            title : "Women's Sabre",
             entrants: $scope.WS 
          }
        ];
    });    
  </script>
  

  <div ng-app="entries" ng-controller="controller">

  <div ng-repeat="event in events">
   <h3 style="float:left">{{event.title}}</h3>  <span style="float:right">{{event.entrants.length}} entered</span>
   <table class="table table-bordered table-striped">
     <tr>      
       <td>Name</td>
       <td>Club</td>       
       <td>Nationality</td>       
     </tr>
      <tr ng-repeat="m in event.entrants">      
       <td>{{m.firstName}} {{m.lastName}}</td>
       <td>{{m.club}}</td> 
       <td>{{m.nationality}}</td>          
     </tr>
   </table> 
   </div>
  </div>
  
  <?php
        /* Get the buffered content into a var */
        $sc = ob_get_contents();
        
        /* Clean buffer */
        ob_end_clean();
        
        /* Return the content as usual */
        return $sc;
    } else {
        return new WP_Error("Error", __("FormID wasn't specified", "IFF_Lookup_Plugin"));
    }
}
add_shortcode('iff_display_form_entries', 'entryLookup');

?>