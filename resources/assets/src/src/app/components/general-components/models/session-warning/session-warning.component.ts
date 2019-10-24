import { ModalDirective } from 'angular-bootstrap-md';
import { Component, OnInit, ViewChild, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-session-warning',
  templateUrl: './session-warning.component.html',
  styleUrls: ['./session-warning.component.scss']
})
export class SessionWarningComponent implements OnInit {
  @ViewChild('frame') modal: ModalDirective;
  countdown: any;
  divide: number;
  constructor() { }

  ngOnInit() {
  }

  showModel(countdown: any, divide: number) {
    this.countdown = countdown;
    this.divide = divide;
    this.modal.show();
  }

  hideModel() {
    this.modal.hide();
  }

}
