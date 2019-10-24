import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { DataService } from '../../storage/data.service';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  constructor(private router: Router, private dataService: DataService) { }

  auCheckActivation() {
    return this.router.navigate(['user/activation']);
  }

  AuthGuard(): boolean {
    return !!localStorage.getItem('AccessToken');
  }

  getToken() {
    return localStorage.getItem('AccessToken');
  }

  accountVerify(): boolean {
    return (localStorage.getItem('AcountStatus') === '1');
  }

  getEmail() {
    return localStorage.getItem('currentUser');
  }

  getCompanyId() {
    return localStorage.getItem('ID');
  }

  setToken(req: any): void {
    if (req) {
      localStorage.removeItem('User');
      localStorage.setItem('User', JSON.stringify(req['data'].user));
      localStorage.setItem('currentUser', JSON.stringify(req['data'].user.email));
      localStorage.setItem('AccessToken', req['data'].accessToken);
      localStorage.setItem('AcountStatus', JSON.stringify(req['data'].user.is_activation));
    }
  }

  setUser(req: any): void {
    if (req) {
        localStorage.removeItem('User');
        localStorage.removeItem('currentUser');
        localStorage.removeItem('AcountStatus');
        localStorage.setItem('User', JSON.stringify(req));
        localStorage.setItem('currentUser', JSON.stringify(req.email));
        localStorage.setItem('AcountStatus', JSON.stringify(req.is_activation));
    }
  }
}
