/**
 * @file
 * Call the 'Map' library with the Drupal settings.
 */

(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
 key: drupalSettings.geolocation.google_map_url_info.key,
 v: "weekly",
});

(function (Drupal, drupalSettings) {

  Drupal.behaviors.rentalMap = {
    attach: function (context, settings) {
      if (context !== document) {
        return;
      }
      //console.log(drupalSettings.locations);
      async function initMap() {
        if(drupalSettings.locations.coords){
          const { Map } = await google.maps.importLibrary("maps");
          const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
          const map = new Map(document.getElementById("rentalmap"), {
            center: { lat: parseFloat(drupalSettings.locations.coords[0]), lng: parseFloat(drupalSettings.locations.coords[1]) },
            zoom: 14,
            mapId: "4504f8b37365c3d0",
          });
          const priceTag = document.createElement("div");
          priceTag.className = "price-tag";
          priceTag.textContent = drupalSettings.locations.title;
          const marker = new AdvancedMarkerElement({
            map,
            position: { lat: parseFloat(drupalSettings.locations.coords[0]), lng: parseFloat(drupalSettings.locations.coords[1]) },
            content: priceTag,
          });
        }
      }
      initMap();
    }
  };

})(Drupal, drupalSettings);
