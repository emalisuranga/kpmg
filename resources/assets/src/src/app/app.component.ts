import { AppLoadService } from './http/shared/app-load.service';
import { AuthService } from './http/shared/auth.service';
import { SessionTimeOutComponent } from './components/general-components/models/session-time-out/session-time-out.component';
import { SessionWarningComponent } from './components/general-components/models/session-warning/session-warning.component';
import { AuthenticationService } from './http/services/authentication.service';
import { Component, ViewChild, AfterViewInit, OnInit } from '@angular/core';

import { Idle, DEFAULT_INTERRUPTSOURCES } from '@ng-idle/core';
import { Keepalive } from '@ng-idle/keepalive';
import { UserService } from './http/services/user.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent implements OnInit, AfterViewInit {

  idleTime = 3600;
  setTimeout = 60;
  idleState = 'Not started.';
  timedOut = false;
  lastPing?: Date = null;
  @ViewChild('sWmodel') warning = new SessionWarningComponent;
  @ViewChild('toModel') TimeOut = new SessionTimeOutComponent;

  constructor(private idle: Idle, private keepalive: Keepalive, private authentication: AuthenticationService, public Auth: AuthService, private load: AppLoadService, private user: UserService) { }

  reset() {
    this.idle.watch();
    this.idleState = 'Started.';
    this.timedOut = false;
  }

  ngOnInit(): void {
      this.time();
  }

  ngAfterViewInit(): void {
    if (this.Auth.AuthGuard()) {
      this.user.getUser().subscribe();
    }
    this.load.initializeApp();
    this.load.initializeProvince();
    this.load.getCityAndGnDivision();
  }

  time() {
    if (this.Auth.AuthGuard()) {
      this.idle.setIdle(this.idleTime);
      this.idle.setTimeout(this.setTimeout);
      this.idle.setInterrupts(DEFAULT_INTERRUPTSOURCES);
      this.idle.onIdleEnd.subscribe(() => this.idleState = 'No longer idle.');
      this.idle.onTimeout.subscribe(() => {
        this.authentication.aulogout().subscribe();
        this.warning.hideModel();
        this.TimeOut.showModel();
        this.timedOut = true;
      });
      this.idle.onIdleStart.subscribe(() => this.idleState = 'You\'ve gone idle!');
      this.idle.onTimeoutWarning.subscribe((countdown) => { this.warning.showModel(countdown, this.setTimeout); });
      this.keepalive.interval(15);
      this.keepalive.onPing.subscribe(() => { this.warning.hideModel(); });
      this.reset();
    }

  }


}
