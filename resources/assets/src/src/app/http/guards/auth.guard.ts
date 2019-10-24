import { Router } from '@angular/router';
import { Injectable, AfterViewInit } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { Observable } from 'rxjs';
import { AuthenticationService } from '../services/authentication.service';
import { AuthService } from '../shared/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {

  constructor(private router: Router, private Authentication: AuthenticationService, private Auth: AuthService) { }

  canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {

    if (localStorage.getItem('AccessToken') != null) {
      if (localStorage.getItem('AcountStatus') === '1') {
        return true;
      }
      this.Auth.auCheckActivation();
      return false;
    }

    this.Authentication.aulogout();
    return false;
  }

}

