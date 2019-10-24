import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SocietyIncorporationComponent } from './society-incorporation.component';

describe('SocietyIncorporationComponent', () => {
  let component: SocietyIncorporationComponent;
  let fixture: ComponentFixture<SocietyIncorporationComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SocietyIncorporationComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SocietyIncorporationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
