import { Component, OnInit, AfterViewInit } from '@angular/core';
import { AuthenticationService } from '../../../http/services/authentication.service';
import { AuthService } from '../../../http/shared/auth.service';
import { GlobleUserService } from '../../../http/shared/globle.user.service';
import { DomSanitizer, SafeResourceUrl, SafeUrl } from '@angular/platform-browser';

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.scss']
})
export class HeaderComponent {

  constructor(
    public authentication: AuthenticationService,
    public Auth: AuthService,
    public user: GlobleUserService,
  ) {}


  cklogOut(): void {
    this.authentication.aulogout().subscribe();
  }

}
