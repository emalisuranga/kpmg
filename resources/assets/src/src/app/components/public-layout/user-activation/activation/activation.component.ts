import { AuthenticationService } from './../../../../http/services/authentication.service';
import { AuthService } from './../../../../http/shared/auth.service';
import { ToastrService } from 'ngx-toastr';
import { Component, OnInit } from '@angular/core';

@Component ({
  selector: 'app-activation',
  templateUrl: './activation.component.html',
  styleUrls: ['./activation.component.scss']
})
export class ActivationComponent implements OnInit {

  public message: string;
  public userEmail: string;
  constructor(
    private authService: AuthService,
    public auth: AuthenticationService,
    private snotifyService: ToastrService) {}

  ngOnInit() {
    this.userEmail = this.authService.getEmail();
  }


  requestLink(): void {
    this.auth.auRequestActivation().subscribe(
      req => {
        this.snotifyService.success('A Verification link has been send to your email account.', 'Success');
      }
    );
  }

}
