# foundlove
一个借助phpstudy，实现的注册登录平台，主要用于模拟暴力破解靶场环境。
## phpstudy构建网站
把本项目当作网站用phpstudy展现出来
### 数据库构建
如图通过phpstudy进入mysql主页，然后按图中顺序，新建foundlove数据库。  
如图构建一个叫做users的表，并令其字段数为3，点击执行。  
将三个数据如图设置，然后保存，只要看到下面的截图即设置成功。  
### 访问验证  
在浏览器中访问刚刚设置的域名，测试是否能成功访问  
如果正常显示表面phpstudy成功，然后在Register地方注册一个新账户，foundlove@outlook.com, 密码014523，如果反馈注册成功，则说明数据库连接成功，可以开始下一步实验。  
## 爆破实验
### 对注册、登录相关信息的收集， 
首先是welcome首页，可以看到在底部轮播滚动的图片上，标有个人公开信息，也就是说，我们可以从这里知道：用户邮箱和编号，在相亲网站中，这种操作是很常见的。  
然后进入注册查看一下，注册的时候会不会有有用的对密码的规则信息：  
可以看到，并没有太多有用的信息，但是经过一些尝试，虽然知道了注册的时候密码至少六位，但是没有提供实质性的思路。  
于是乎，来到第三个导航栏标签登录 ”Login” ，发现跟注册一个很大的不同就是，有忘记密码选项：  
这里面一般情况下就会包含有跟密码相关的信息，点进去之后发现果然：  
在注意事项的最后一条，很明显可以看到，ID（也就是最一开始我们看到的编号）是相亲现场分配的，密码则是身份证后六位。  
至此，已经可以确定的是：  
>登录时需要两个信息：邮箱和密码；  
>密码的规则是身份证后六位；  

### 经过查看网页公开用户信息，选定想要暴破的目标账户
在首页轮播中，选中一位用户，可以看见其邮箱为：foundlove@outlook.com，编号为：124；   
我们选定这个账户为攻击目标。  
### 对密码规则进行构建密码本（C++版本、python版本择一进行）
构建密码本：按理来说六位数可以生成10的6次方个数，也就是1000000，但是因为是身份证后六位，所以有所区别：  
>前两位是日期，所以一定是 ”01-31” 之间的数；  
>倒数第二位是性别验证，如果能知道攻击目标的性别，这里一下也可以去掉一半的无效密码字典，但是这里并没有明确知道性别；  
>最后一位校验位可能出现X，但是概率很小，而且密码设定的时候，X通常会用某数代替，比如说0；  

现在规则说清楚了之后，可以开始写脚本生成密码本：   
`Python`：  
在id_number.py文件中构建如下代码：  
``` python
print ('password set')
passwords = open('passwords.txt','w')
for i in range(1, 32):
    day = str(i)
    if (i < 10):
        day = '0' + day
    for j in range(100):
        if (j < 10):
            dayafter = '0' + str(j)
        else:
            dayafter = str(j)        
        for k in range(100):
            end = str(k)
            if(k<10):
                end = '0' + end
            password = day+dayafter+end
            passwords.write('\n'+password)
            print(password+'\n')
passwords.close()
```  
然后在控制台运行（这一步在Linux和Windows环境下操作相同），`python  id_number.py`，即可生成密码本passwords.txt  
### BP入门，代理等配置设置
现在，目标账户已经锁定，并且知道用户ID和注册邮箱，然后密码本已经生成，接下来就正式进入爆破阶段，在此之前，需要设置一下Burp suite(BP)，关于BP在工具阶段已经介绍了，这里就直接开始网络代理设置部分的介绍：  
>使用火狐浏览器，打开新标签，并输入：about:preferences ，在常规这一部分，滚到最下方有个网络设置  
>点击进入，选择手动代理配置，并且把内容配置成如图，目的是使用127.0.0.1代理，这样就可以在bp进行拦截，相当于每次传输都要经过bp，这样我们就可以利用bp对传输信息进行分析利用：  
>接下来进入bp的proxy一栏，打开intercept，显示如图：  
>在火狐浏览器打开Found Love的网站，在Login登录选项随机输入密码，如果bp效果如下图，则代表设置成功：  
### BP加密码本暴破选定目标账户
此时我们看到的Raw里的内容就是传输的包，我们分析一下，很明显可以看到，email=fondlove%40outlook.com&password=57657456&rem=1&login=，
最后一行有两块可疑内容：email和password，57657456就是刚刚随机输入的密码，如果能够把这一块进行设置，加上密码本就能找出密码，点击Action，进入Send to Intruder。  
### 接下来就是爆破的核心几步：
#### （1）进入Intruder，可以看到target部分已经预设好，这里就不需要再管了，因为这是从之前截获的包里提取出来的；    
#### （2）进入positions，还记得我们一开始说的值得怀疑的地方吗？这里可以看到，bp已经帮我们加了§括起来，但是除了怀疑的password别的也在括起来了。
这里需要说明的就是，bp把所有可变因素都给括起来的，我们只需要password是可变的，因此可以选中不需要的元素，然后用Clear§按钮去掉§符号  
#### （3）需要额外说明，最上方的attack type：  
① **Sniper（狙击手模式）**  
针对单一密码，假设确定了两个位置A和B，然后密码包payload里有两个密码1、2，那么攻击模式如下：  
|Attack No.|Position A|Position B|
|:----:|:----:|:----:|
|0|1|Null|
|1|2|Null|
|2|Null|1|
|3|Null|2|  

一次只会对一个位置进行攻击！
② **Battering ram（攻城锤模式）**  
与sniper模式不同的地方在于，同样情况下，攻击次数减半，每次两个位置用同样的密码，如表：  
|Attack No.|Position A|Position B|
|:----:|:----:|:----:|
|0|1|1|
|1|2|2|  

③ **Pitchfork（叉子模式）**  
跟前两种不同的地方在于，可以多组密码本payload，又于battering ram相同的地方在于，一一对应，现在添加包含3、4的密码本payload，暴力破解过程如表：  
|Attack No.|Position A|Position B|
|:----:|:----:|:----:|
|0|1|3|
|1|2|4|  

④ **Cluster bomb（炸弹模式）**
跟叉子模式相似的是多个密码本对应多个位置，不同的是不再是一一对应，而是交叉组合，每一个密码本里的密码都对应于另一密码本所有密码，如表：  
|Attack No.|Position A|Position B|
|:----:|:----:|:----:|
|0|1|3|
|1|2|3|
|2|1|4|
|3|2|4|  

**此处我们仅在password位置设置密码本，因此选择狙击手模式。**  
	
#### （4）进入Payloads，此时在传输包方面的设置已经完成，只剩下加载密码本了，Payload Options处有load功能，点击加载我们之前创建的密码本：  
#### （5）点击Start Attack进入爆破。  
#### （6）等待爆破结束及说明   
当进入如图界面时，剩下的就是等待了，三个框中，最底下的代表的就是进度，代表当前已经跑过多少密码了；中间的小框有两个：Request、Response，一个是请求界面，一个是回返界面，当我们想要查看密码是否正确的时候，可以点击Response查看；当我们想要看时候有正确密码产生的时候，可以点击length（代表的是反应时长，因为密码正确和错误所反馈的时间是不同的），发现异常时长时，即可点击Response查看；  
#### （7）爆破结束  
可以看到密码在014523处时间变少，跟其他的都不一样，并且在response里看到有Login success！字样，说明爆破成功，此时回到foundlove网站，用邮箱密码登录即可看到如图效果。（当我们再次操作浏览器时，要在bp的proxy处关掉intercept）
当想要载再次进行此实验的时候可以点击logout退出即可
