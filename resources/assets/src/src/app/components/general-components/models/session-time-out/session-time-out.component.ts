import { ModalDirective } from 'angular-bootstrap-md';
import { Component, OnInit, ViewChild } from '@angular/core';

@Component({
  selector: 'app-session-time-out',
  templateUrl: './session-time-out.component.html',
  styleUrls: ['./session-time-out.component.scss']
})
export class SessionTimeOutComponent implements OnInit {
  @ViewChild('frame') modal: ModalDirective;
  constructor() { }

  ngOnInit() {
  }

  showModel() {
    this.modal.show();
  }
}
