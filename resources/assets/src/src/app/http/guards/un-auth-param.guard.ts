import { NgxSpinnerService } from 'ngx-spinner';
import { map } from 'rxjs/operators';
import { NameResarvationService } from './../services/name-resarvation.service';
import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { Observable } from 'rxjs';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class UnAuthParamGuard implements CanActivate {
  private has: string;
  constructor(private res: NameResarvationService, private router: Router) { }
  canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
    this.validParamiter(next.params['id']);

    if (this.has === undefined || this.has === 'true') {
      this.router.navigate(['/dashboard/home']);
      return false;
    } else {
      return true;
    }

  }

  validParamiter(param: number) {
    return this.res.nameReSubmitParamiterValidate(param)
      .subscribe(req => {
        this.has = req['has'];
      });
  }
}
