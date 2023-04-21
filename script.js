
// add event
var el = document.getElementById("addEventModal");
if (el) {
  el.addEventListener('show.bs.modal', (e) => {
    document.getElementById("inputVenueID").value = e.relatedTarget.dataset.venueid;
  });
}

// add session
var el = document.getElementById("addSessionModal");
if (el) {
  el.addEventListener('show.bs.modal', (e) => {
    document.getElementById("inputEventID").value = e.relatedTarget.dataset.eventid;
  });
}

// update user / attendee info
var el = document.getElementById("editUserModal");
if (el) {
  el.addEventListener('show.bs.modal', (e) => {
    document.getElementById("updatedUserID").value = e.relatedTarget.dataset.userid;
    document.getElementById("upatedUserName").value = e.relatedTarget.dataset.username;
  });
}

  // update venue
  var el = document.getElementById("editVenueModal");
if (el) {
  el.addEventListener('show.bs.modal', (e) => {
    document.getElementById("inputUpdatedVenue").value = e.relatedTarget.dataset.venuename;
    document.getElementById("inputUpdatedVenueID").value = e.relatedTarget.dataset.venueid;
  });
}

// update event
var el = document.getElementById("editEventModal");
  if (el) {
    el.addEventListener('show.bs.modal', (e) => {
      document.getElementById("inputUpdatedEvent").value = e.relatedTarget.dataset.eventname;
      document.getElementById("inputUpdatedEventID").value = e.relatedTarget.dataset.eventid;
      document.getElementById("inputUpdatedEventVenueID").value = e.relatedTarget.dataset.eventvenueid;
    });
  }

// update session
var el = document.getElementById("editSessionModal");
  if (el) {
    el.addEventListener('show.bs.modal', (e) => {
      document.getElementById("inputUpdatedSession").value = e.relatedTarget.dataset.sessionname;
      document.getElementById("inputUpdatedSessionID").value = e.relatedTarget.dataset.sessionid;
      document.getElementById("inputUpdatedSessionEventID").value = e.relatedTarget.dataset.sessioneventid;
    });
  }

// edit attendee of event
var el = document.getElementById("editAttendeeModal");
  if (el) {
    el.addEventListener('show.bs.modal', (e) => {
      document.getElementById("attendeeID").value = e.relatedTarget.dataset.attendeeid;
      document.getElementById("attendeeName").value = e.relatedTarget.dataset.attendeename;
    });
  }