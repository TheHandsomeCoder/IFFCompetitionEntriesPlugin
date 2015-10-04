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
        
        $unfilteredEntries = GFAPI::get_entries($formID, $search_criteria, $sorting, $paging, $total_count);
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
    
    array_push($eventsEntered, humanReadableEntry($value['19']));  
    array_push($eventsEntered, humanReadableEntry($value['22']));  
    array_push($eventsEntered, humanReadableEntry($value['32']));  
    array_push($eventsEntered, humanReadableEntry($value['26']));  
    array_push($eventsEntered, humanReadableEntry($value['27']));  
    array_push($eventsEntered, humanReadableEntry($value['33']));  

    return array(       
        'firstName' => $value['2.3'],
        'lastName' => $value['2.6'],
        'club' => ($value['10'] != 'Other (please specify)') ? $value['10'] : $value['11'],
        'events' => $eventsEntered    
    );
}

function humanReadableEntry($value)
{
    $values = array(
        "Women's Foil|10" => 'WF',
        "Women's Foil|15" => 'WF',
       
        "Women's Sabre|10" => 'WS',
        "Women's Sabre|15" => 'WS',

        "Women's Épée|10" => 'WE',
        "Women's Épée|15" => 'WE',

        "Men's Foil|10" => 'MF',
        "Men's Foil|15" => 'MF',
       
        "Men's Sabre|10" => 'MS',
        "Men's Sabre|15" => 'MS',

        "Men's Épée|10" => 'ME',
        "Men's Épée|15" => 'ME'
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

    });    
  </script>
  

  <div ng-app="entries" ng-controller="controller">

   <h3>Mens Epee</h3>
   <table class="table table-bordered table-striped">
     <tr>      
       <td>Name</td>
       <td>Club</td>       
     </tr>
      <tr ng-repeat="m in ME">      
       <td>{{m.firstName}} {{m.lastName}}</td>
       <td>{{m.club}}</td>      
     </tr>
   </table>  

   <h3>Mens Foil</h3>
   <table class="table table-bordered table-striped">
     <tr>      
       <td>Name</td>
       <td>Club</td>       
     </tr>
      <tr ng-repeat="m in MF">      
       <td>{{m.firstName}} {{m.lastName}}</td>
       <td>{{m.club}}</td>      
     </tr>
   </table>  

   <h3>Mens Sabre</h3>
   <table class="table table-bordered table-striped">
     <tr>      
       <td>Name</td>
       <td>Club</td>       
     </tr>
      <tr ng-repeat="m in MS">      
       <td>{{m.firstName}} {{m.lastName}}</td>
       <td>{{m.club}}</td>      
     </tr>
   </table>  

   <h3>Womens Epee</h3>
   <table class="table table-bordered table-striped">
     <tr>      
       <td>Name</td>
       <td>Club</td>       
     </tr>
      <tr ng-repeat="m in WE">      
       <td>{{m.firstName}} {{m.lastName}}</td>
       <td>{{m.club}}</td>      
     </tr>
   </table>  

   <h3>Womens Foil</h3>
   <table class="table table-bordered table-striped">
     <tr>      
       <td>Name</td>
       <td>Club</td>       
     </tr>
      <tr ng-repeat="m in WF">      
       <td>{{m.firstName}} {{m.lastName}}</td>
       <td>{{m.club}}</td>      
     </tr>
   </table>  

 <h3>Womens Sabre</h3>
   <table class="table table-bordered table-striped">
     <tr>      
       <td>Name</td>
       <td>Club</td>       
     </tr>
      <tr ng-repeat="m in WS">      
       <td>{{m.firstName}} {{m.lastName}}</td>
       <td>{{m.club}}</td>      
     </tr>
   </table>  


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