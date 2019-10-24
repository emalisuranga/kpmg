import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router } from '@angular/router';
import { Observable } from 'rxjs';
import { AuthenticationService } from '../services/authentication.service';

@Injectable({
  providedIn: 'root'
})
export class ReqAuthPasswordGuard implements CanActivate {
  constructor(private router: Router, private Authentication: AuthenticationService) {
    localStorage.clear();
  }
  canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
    if (next.queryParams['email'] !== undefined && next.queryParams['token'] !== undefined) {
       return true;
    } else {
      this.router.navigate(['/home']);
      return false;
    }


  }
}
