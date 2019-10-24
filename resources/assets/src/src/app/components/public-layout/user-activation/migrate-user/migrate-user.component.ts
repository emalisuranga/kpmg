import { Component, OnInit } from '@angular/core';
import { AuthenticationService } from 'src/app/http/services/authentication.service';
import { ToastrService } from 'ngx-toastr';
import { AuthService } from 'src/app/http/shared/auth.service';

@Component({
  selector: 'app-migrate-user',
  templateUrl: './migrate-user.component.html',
  styleUrls: ['./migrate-user.component.scss']
})
export class MigrateUserComponent implements OnInit {
  public message: string;
  public userEmail: string;
  constructor(
    private authService: AuthService,
    public auth: AuthenticationService,
    private snotifyService: ToastrService) { }

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
